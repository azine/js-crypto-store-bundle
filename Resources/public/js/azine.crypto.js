/** azineJavaScriptCryptoStore */
var ajscs = {}

/**
 * Convert a base64 string in a Blob according to the data and contentType.
 *
 * @param b64Data {String} Pure base64 string without contentType
 * @param contentType {String} the content type of the file i.e (image/jpeg - image/png - text/plain)
 * @param sliceSize {Int} SliceSize to process the byteCharacters
 * @see http://stackoverflow.com/questions/16245767/creating-a-blob-from-a-base64-string-in-javascript
 * @return Blob
 */
ajscs.base64ToBlob = function (b64Data, contentType, sliceSize) {
    contentType = contentType || '';
    sliceSize = sliceSize || 512;

    var byteCharacters = atob(b64Data);
    var byteArrays = [];

    for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        var slice = byteCharacters.slice(offset, offset + sliceSize);

        var byteNumbers = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }

        var byteArray = new Uint8Array(byteNumbers);

        byteArrays.push(byteArray);
    }

    var blob = new Blob(byteArrays, {type: contentType});
    return blob;
}

/**
 * Encrypt and upload the file from the form
 * @param event
 * @returns {boolean}
 */
ajscs.encryptAndUpload = function(clickEvent) {
    // encryption config from form
    var form = clickEvent.target;

    // form data
    var file = form.elements.jsCryptoUpload_file.files[0];

    // validate file size
    if(file.size > form.getAttribute("data-max-file-size")){
        ajscs.showMessage(ajscsMessages.fileTooBigErrorMessage);
        return false;
    }

    // read file to encrypt
    var reader = new FileReader();
    reader.onloadend = function (loadendEvent) {
        ajscs.updateProgressIndicator(ajscsMessages.encryptingFileProgressText);
        var encryptedData = sjcl.encrypt(
                form.elements.jsCryptoUpload_password.value,
                loadendEvent.target.result,
                {
                    v: 1,
                    iter: ajscsConfig.iterations,
                    ks: ajscsConfig.ks,
                    ts: ajscsConfig.ts,
                    mode: ajscsConfig.mode,
                    cipher: ajscsConfig.cipher
                }
            );
        ajscs.storeData(form, encryptedData);
    };
    reader.readAsDataURL(file);
    ajscs.updateProgressIndicator(ajscsMessages.readingFileProgressText);
    return false;
};

/**
 * Store the encrypted file
 * @param form
 * @param encryptedData
 * @return void
 */
ajscs.storeData = function(form, encryptedData){

    var formInputs = form.elements;
    var file = formInputs.jsCryptoUpload_file.files[0];

    // send encrypted file-content to server
    var formData = new FormData();
    formData.append('fileData', encryptedData);
    formData.append('mimeType', file.type);
    formData.append('fileName', file.name);
    formData.append('expiry', formInputs.jsCryptoUpload_expiry.value);
    formData.append('description', formInputs.jsCryptoUpload_description.value);
    formData.append('groupToken', formInputs.jsCryptoUpload_groupToken.value);
    formData.append('_token', formInputs.jsCryptoUpload__token.value);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', form.action);
    xhr.onload = function() {
        if (xhr.status === 200) {
            ajscs.clearFormAddFileToList(form, JSON.parse(xhr.responseText));
        } else {
            ajscs.showMessage(ajscsMessages.requestFailedErrorMessage + xhr.status);
            ajscs.hideProgressIndicator();
        }
    };
    xhr.onerror = function(error){
        ajscs.hideProgressIndicator();
        ajscs.showMessage(error, 'error');
    }
    ajscs.updateProgressIndicator(ajscsMessages.uploadingProgressText);
    xhr.send(formData);

}

/**
 * Download and Decrypt the file specified in the form
 * @param event
 * @returns {boolean}
 */
ajscs.downloadAndDeCrypt = function(clickEvent){
    var form = clickEvent.target;

    // prepare xhr-form
    var formData = new FormData();
    formData.append('token', form.elements.jsCryptoDownload_token.value);
    formData.append('_token', form.elements.jsCryptoDownload__token.value);

    // get file meta data from server
    var fileMetaDataXhr = new XMLHttpRequest();
    fileMetaDataXhr.open('POST', form.action);
    fileMetaDataXhr.onload = function() {
        if (fileMetaDataXhr.status === 200) {

            var fileMetaData = JSON.parse(fileMetaDataXhr.responseText);
            var fileDataXhr = new XMLHttpRequest();
            //fileDataXhr.responseType = "blob";
            fileDataXhr.open('GET', fileMetaData['fileUrl']);
            fileDataXhr.onload = function (loadendEvent) {
                if (fileDataXhr.status === 200) {

                    ajscs.decryptWithPassword = function () {
                        ajscs.updateProgressIndicator(ajscsMessages.decryptingProgressText);
                        ajscs.password = document.getElementById("jsCryptoDownload_password").value

                        try {
                            var decryptedData = sjcl.decrypt(ajscs.password,
                                fileDataXhr.response,
                                {
                                    v: 1,
                                    iter: ajscsConfig.iterations,
                                    ks: ajscsConfig.ks,
                                    ts: ajscsConfig.ts,
                                    mode: ajscsConfig.mode,
                                    cipher: ajscsConfig.cipher
                                }
                            );
                        } catch (e) {
                            ajscs.showMessage(ajscsMessages.wrongPasswordErrorMessage, 'error');
                            ajscs.askForPassword(ajscs.decryptWithPassword);
                            return;
                        }
                        ajscs.updateProgressIndicator(ajscsMessages.preparingDownloadProgressText);

                        var block = decryptedData.split(";");
                        var mimeType = block[0].split(":")[1];
                        var realData = block[1].split(",")[1];
                        var blob = ajscs.base64ToBlob(realData, mimeType);

                        //for microsoft IE
                        if (window.navigator && window.navigator.msSaveOrOpenBlob) {
                            window.navigator.msSaveOrOpenBlob(blob, fileMetaData['fileName']);
                        } else { //other browsers
                            var link = document.getElementById("downloadLink");
                            link.href = window.URL.createObjectURL(blob);
                            link.download = fileMetaData['fileName'];
                            link.click();
                        }

                        setTimeout(function () {
                            ajscs.hideProgressIndicator();
                        }, 1000);
                    };
                    ajscs.askForPassword(ajscs.decryptWithPassword);
                } else {
                    ajscs.hideProgressIndicator();
                    ajscs.showMessage(ajscsMessages.requestFailedErrorMessage + fileDataXhr.status, 'error');
                }

            };
            fileDataXhr.onerror = function(error){
                ajscs.hideProgressIndicator();
                ajscs.showMessage(error, 'error');
            };
            fileDataXhr.send(null);

        } else {
            ajscs.hideProgressIndicator();
            ajscs.showMessage(ajscsMessages.requestFailedErrorMessage + fileMetaDataXhr.status, 'error');
        }
    };
    fileMetaDataXhr.onerror = function(error){
        ajscs.hideProgressIndicator();
        ajscs.showMessage(error, 'error');
    }

    ajscs.updateProgressIndicator(ajscsMessages.fetchingFromServerProgressText);
    fileMetaDataXhr.send(formData);
    return false;
}

/**
 * Show modal aksing for the password.
 * @param decryptWithPasswordFunction the function to be called, to decrypt with the supplied password
 */
ajscs.askForPassword = function(decryptWithPasswordFunction) {
    // show the password dialog
    ajscs.hideProgressIndicator();
    var previousPassword = "";
    if(ajscsMessages.password != undefined){
        previousPassword = ajscsMessages.password;
    }
    document.getElementById("jsCryptoDownload_password").value = previousPassword;
    document.getElementById("jsCryptoStorePasswordModal").style.display = "";
    document.getElementById("jsCryptoDownload_passwordSubmit").onclick = decryptWithPasswordFunction;
};

/**
 * Clear the upload-form and add the new encrypted file to the file-list
 * @param form
 * @param response
 */
ajscs.clearFormAddFileToList = function(form, response){
    var token = response.token;
    var groupToken = response.groupToken;
    var group = response.group;
    var mimeType = form.elements.jsCryptoUpload_file.files[0].type;
    var fileName = form.elements.jsCryptoUpload_file.files[0].name;
    var description = form.elements.jsCryptoUpload_description.value;
    var expiry = response.expiryDate.date.substr(0, 16);

    // clear form
    form.reset();

    // show file/download list
    document.getElementById("jsCryptoFileListDiv").style.display = "inherit";

    // add the group file list if it not exists
    var groupListDiv = document.getElementById("jsCryptoFileListDiv-"+groupToken);
    if(groupListDiv == undefined) {
        groupListDiv = document.getElementById("jsCryptoFileListDiv-groupTokenDummy").cloneNode(true);
        groupListDiv.style.display = "inherit";
        groupListDiv.id = "jsCryptoFileListDiv-"+groupToken;
        groupListDiv.innerHTML = groupListDiv.innerHTML.replace(/groupTokenDummy/g, groupToken);
        groupListDiv.innerHTML = groupListDiv.innerHTML.replace(/groupDummy/g, group);
        document.getElementById("jsCryptoFileListDiv").appendChild(groupListDiv);
    }
    var groupList = groupListDiv.getElementsByTagName("ul").item(0);

    // add link to list
    var newlistElement = document.getElementById("file-tokenDummy").cloneNode(true);
    var innerHTML = newlistElement.innerHTML;
    innerHTML = innerHTML.replace(/%expiryDate%/g, expiry);
    innerHTML = innerHTML.replace(/%description%/g, description);
    innerHTML = innerHTML.replace(/tokenDummy/g, token);
    innerHTML = innerHTML.replace(/groupTokenDummy/g, groupToken);
    innerHTML = innerHTML.replace(/%fileName%/g, fileName);
    innerHTML = innerHTML.replace(/%mimeType%/g, mimeType);
    newlistElement.setAttribute("id", "file-"+token);
    newlistElement.setAttribute("style", "");
    newlistElement.innerHTML = innerHTML;
    groupList.appendChild(newlistElement);
    ajscs.hideProgressIndicator();
}

/**
 * Delete file from server and from the file-list
 * @param url
 * @returns {boolean}
 */
ajscs.deleteFile = function(url){
    if(confirm(ajscsMessages.confirmDeleteText)){
        var xhr = new XMLHttpRequest();
        xhr.open('DELETE', url);
        xhr.onload = function(){
            if(xhr.status === 200){
                var response = JSON.parse(xhr.responseText);

                // remove the list-entry
                var listElement = document.getElementById("file-" + response.token);
                var list = listElement.parentNode;
                list.removeChild(listElement);

                // remove the group if this was the last entry
                if(list.getElementsByTagName("li").length == 0){
                    list.parentNode.parentNode.removeChild(list.parentNode);
                }
            }
        }
        xhr.send(null);
    }
    return false;
}

/**
 * Hide the progress indicator modal div
 */
ajscs.hideProgressIndicator = function() {
    document.getElementById("jsCryptoStoreProgressIndicator").style.display = "none";
}

/**
 * Show the progress indicator modal div
 * @param msg text to show
 */
ajscs.updateProgressIndicator = function(msg){
    document.getElementById("jsCryptoStorePasswordModal").style.display = "none";
    document.getElementById("jsCryptoStoreProgressIndicator").style.display = "inherit";
    document.getElementById("jsCryptoStoreProgressIndicatorTxt").innerHTML = msg;
}

ajscs.togglePassword = function() {
    var pwdInput = document.getElementById("jsCryptoDownload_password");
    var newType = pwdInput.getAttribute('type') == 'password' ? 'text' : 'password';
    pwdInput.setAttribute('type', newType);
}

/**
 * Show a message to the user
 */
ajscs.showMessage = function(message, level){
    // TODO implement this
    if(level == undefined || level == 'info') {
        console.log(message);
    } else if(level == 'warn'){
        console.warn(message);
    } else if(level == 'error'){
        console.error(message);
    }
    alert(message);
}

/**
 * Update the page title depending on the selected file and/or file-group, so password managers can search for existing password entries
 */
ajscs.updateTitle = function(){
    var baseTitle = ajscsMessages.pageBaseTitle;
    var groupInput = document.getElementById("jsCryptoUpload_groupToken");
    var fileInput = document.getElementById("jsCryptoUpload_file");
    if(groupInput != null && groupInput.value != ""){
        document.title = baseTitle + " (" + groupInput.value + ")";

    } else if(fileInput != null && fileInput.files.length == 1) {
        var file = fileInput.files[0].name;
        document.title = baseTitle + " (" + file + ")";
    }
}
ajscs.updateTitle();

ajscs.updateDescription = function () {
    var descriptionField = document.getElementById("jsCryptoUpload_description");
    if(descriptionField.value == "") {
        var fileName = document.getElementById("jsCryptoUpload_file").files[0].name;
        // remove extension
        fileName = fileName.replace(/\.[^/.]+$/, "");

        // change "-" and "_" to " "
        fileName = fileName.replace(/(_|-)/g," ");
        descriptionField.value = fileName;
    }
}