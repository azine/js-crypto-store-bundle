<?php

namespace Azine\JsCryptoStoreBundle\Controller;

use Azine\JsCryptoStoreBundle\Entity\EncryptedFile;
use Azine\JsCryptoStoreBundle\Entity\Repositories\EncryptedFileRepository;
use Azine\JsCryptoStoreBundle\Form\DownloadEncryptedFileType;
use Azine\JsCryptoStoreBundle\Form\UploadEncryptedFileType;
use Azine\JsCryptoStoreBundle\Service\OwnerProviderInterface;
use MuBu\DealAnalysisBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller provides actions related the encrypted files.
 */
class EncryptedFileController extends Controller
{
    private $ownerProvider;
    private $encryptionCipher;
    private $encryptionIterations;
    private $encryptionKs;
    private $encryptionTs;
    private $encryptionMode;
    private $maxFileSize;
    private $defaultLifeTime;

    public function __construct(OwnerProviderInterface $ownerProvider, $encryptionCipher, $encryptionIterations, $encryptionKs, $encryptionTs, $encryptionMode, $maxFileSize, $defaultLifeTime)
    {
        $this->ownerProvider = $ownerProvider;
        $this->encryptionCipher = $encryptionCipher;
        $this->encryptionIterations = $encryptionIterations;
        $this->encryptionKs = $encryptionKs;
        $this->encryptionTs = $encryptionTs;
        $this->encryptionMode = $encryptionMode;
        $this->maxFileSize = $maxFileSize;
        $this->defaultLifeTime = $defaultLifeTime;
    }

    /**
     *  Displays a dashboard
     * - show all files for the current user
     * - show all files for the admin
     * - show an upload-form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function dashboardAction(Request $request)
    {
        $uploadForm = $this->createForm(UploadEncryptedFileType::class);
        $uploadForm->handleRequest($request);

        $downloadForm = $this->createForm(DownloadEncryptedFileType::class);
        $downloadForm->handleRequest($request);

        // show users files => delete button & forms for download
        /** @var EncryptedFileRepository $encryptedFileRepository */
        $encryptedFileRepository = $this->getDoctrine()->getRepository(EncryptedFile::class);
        $userFiles = $encryptedFileRepository->findForDashBoard($this->ownerProvider->getOwnerId());
        $groupTokens = $encryptedFileRepository->getGroupTokensForUser($this->ownerProvider->getOwnerId());

        return $this->render('AzineJsCryptoStoreBundle::dashboard.html.twig',
            array(
                'groupTokens' => $groupTokens,
                'encryptionCipher' => $this->encryptionCipher,
                'encryptionIterations' => $this->encryptionIterations,
                'encryptionKs' => $this->encryptionKs,
                'encryptionTs' => $this->encryptionTs,
                'encryptionMode' => $this->encryptionMode,
                'maxFileSize' => $this->maxFileSize,
                'userFiles' => $userFiles,
                'uploadForm' => $uploadForm->createView(),
                'downloadForm' => $downloadForm->createView(),
            ));
    }

    /**
     * Displays a form to download a specific file.
     *
     * @param string $token
     *
     * @return Response
     */
    public function downloadAction(Request $request, $groupToken, $token = null)
    {
        $files = $this->getDoctrine()->getRepository(EncryptedFile::class)->findForDownload($groupToken, $token);

        $forms = array();
        foreach ($files as $nextFile) {
            // show file form for download
            $form = $this->createForm(DownloadEncryptedFileType::class);
            $form->get('token')->setData($nextFile['token']);
            $forms[] = $form->createView();
        }
        $groupToken = substr($files[0]['groupToken'], 0, strrpos($files[0]['groupToken'], '-'));

        return $this->render('AzineJsCryptoStoreBundle::download.html.twig',
            array(
                'files' => $files,
                'groupToken' => $groupToken,
                'downloadForms' => $forms,
                'encryptionCipher' => $this->encryptionCipher,
                'encryptionIterations' => $this->encryptionIterations,
                'encryptionKs' => $this->encryptionKs,
                'encryptionTs' => $this->encryptionTs,
                'encryptionMode' => $this->encryptionMode,
                'maxFileSize' => $this->maxFileSize,
            ));
    }

    /**
     * Handle uploading, downloading or removing a file.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fileAction(Request $request)
    {
        $response = new JsonResponse();
        $responseData = array();
        if ('POST' === $request->getMethod()) {
            // store the submited file
            if (null != $request->get('fileData')) {
                $responseData = $this->storeFile($request);
            } else {
                $token = $request->get('token');
                /** @var EncryptedFile $encryptedFile */
                $encryptedFile = $this->getDoctrine()->getRepository(EncryptedFile::class)->findOneBy(array('token' => $token));
                $responseData = $this->getFileMetaData($encryptedFile);
            }
        } elseif ('DELETE' === $request->getMethod()) {
            $responseData = $this->deleteFile($request);
        }

        return $response->setData($responseData);
    }

    private function getFileMetaData(EncryptedFile $encryptedFile)
    {
        $responseData = array();
        $responseData['mimeType'] = $encryptedFile->getMimeType();
        $responseData['fileName'] = $encryptedFile->getFileName();

        /** @var Packages $manager */
        $manager = $this->get('assets.packages');
        $responseData['fileUrl'] = $manager->getUrl('bundles/azinejscryptostore/files').substr($encryptedFile->getFile(), strrpos($encryptedFile->getFile(), '/'));

        return $responseData;
    }

    private function storeFile(Request $request)
    {
        $fileData = $request->get('fileData');
        $fileName = $request->get('fileName');
        $mimeType = $request->get('mimeType');
        $description = $request->get('description');
        $expiry = $request->get('expiry');
        $groupToken = $request->get('groupToken');
        if (null == $groupToken) {
            $groupToken = ' ';
        }
        $groupToken .= '-'.md5($this->ownerProvider->getOwnerId());
        $encryptedFile = new EncryptedFile();
        $storageDirectory = __DIR__.'/../Resources/public/files/';
        $storageFileName = tempnam($storageDirectory, 'encrypted-');
        chmod($storageFileName, 0664);
        file_put_contents($storageFileName, $fileData);
        $encryptedFile->setFile($storageFileName);
        $encryptedFile->setFileName($fileName);
        $encryptedFile->setMimeType($mimeType);
        $encryptedFile->setDescription($description);
        if ('' == $expiry) {
            $expiry = $this->defaultLifeTime;
        }
        $encryptedFile->setExpiry(new \DateTime($expiry));
        $fileToken = md5($fileData);
        $encryptedFile->setToken($fileToken);
        $encryptedFile->setGroupToken($groupToken);
        $encryptedFile->setOwnerId($this->ownerProvider->getOwnerId());
        $em = $this->getDoctrine()->getManager();
        $em->persist($encryptedFile);
        $em->flush();
        $responseData['token'] = $fileToken;
        $responseData['groupToken'] = $groupToken;
        $responseData['group'] = substr($groupToken, 0, strrpos($groupToken, '-'));
        $responseData['expiryDate'] = $encryptedFile->getExpiry();

        return $responseData;
    }

    private function deleteFile(Request $request)
    {
        $fileToken = $request->get('token');
        $em = $this->getDoctrine()->getManager();
        /** @var EncryptedFile $encryptedFile */
        $encryptedFile = $em->getRepository(EncryptedFile::class)->findOneBy(array('token' => $fileToken));
        if ($encryptedFile) {
            $storageFileName = $encryptedFile->getFile();
            $em->remove($encryptedFile);
            $em->flush();
            unlink($storageFileName);
        }
        $responseData['msg'] = 'file deleted';
        $responseData['token'] = $fileToken;

        return $responseData;
    }
}
