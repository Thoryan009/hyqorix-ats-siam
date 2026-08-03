<?php
// app/Services/OcrService.php

namespace App\Services;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\ApiCore\ApiException;

class OcrService
{
    protected $client;

    public function __construct()
    {
        $credentialsPath = env('GOOGLE_APPLICATION_CREDENTIALS');

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            throw new \Exception('Google Cloud credentials file not found: ' . $credentialsPath);
        }

        $this->client = new ImageAnnotatorClient([
            'credentials' => $credentialsPath
        ]);
    }

    public function extractText($imagePath)
    {
        if (!file_exists($imagePath)) {
            throw new \Exception('Image file not found: ' . $imagePath);
        }

        $imageContent = file_get_contents($imagePath);

        if (!$imageContent) {
            throw new \Exception('Failed to read image file');
        }

        try {
            // Create Image object
            $image = new Image();
            $image->setContent($imageContent);

            // Create Feature for DOCUMENT_TEXT_DETECTION (best for OCR)
            $feature = new Feature();
            $feature->setType(Type::DOCUMENT_TEXT_DETECTION);

            // Create AnnotateImageRequest
            $request = new AnnotateImageRequest();
            $request->setImage($image);
            $request->setFeatures([$feature]);

            // Create BatchAnnotateImagesRequest
            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$request]);

            // Make API call
            $response = $this->client->batchAnnotateImages($batchRequest);

            // Get responses
            $responses = $response->getResponses();

            if (count($responses) === 0) {
                throw new \Exception('No response from Google Vision API');
            }

            $imageResponse = $responses[0];

            // Check for errors
            if ($imageResponse->hasError()) {
                $error = $imageResponse->getError();
                throw new \Exception('Google Vision API error: ' . $error->getMessage());
            }

            // Extract text
            $annotation = $imageResponse->getFullTextAnnotation();
            $text = $annotation ? $annotation->getText() : '';

            return $text;

        } catch (ApiException $e) {
            $errorMessage = $this->parseApiError($e);
            throw new \Exception($errorMessage);
        }
    }

    /**
     * Parse Google API errors into user-friendly messages
     */
    private function parseApiError(ApiException $e): string
    {
        $status = $e->getStatus();
        $basicInfo = $e->getBasicMessage();

        // Check for billing disabled
        if (stripos($basicInfo, 'BILLING_DISABLED') !== false || stripos($basicInfo, 'billing to be enabled') !== false) {
            return "⚠️ Google Cloud Billing Required\n\n" .
                "The Google Cloud Vision API requires billing to be enabled on your project.\n\n" .
                "Steps to fix:\n" .
                "1. Visit: https://console.cloud.google.com/billing\n" .
                "2. Link a billing account to your project\n" .
                "3. Enable the Vision API: https://console.cloud.google.com/apis/library/vision.googleapis.com\n" .
                "4. Wait 5-10 minutes for changes to propagate\n\n" .
                "Original error: " . $basicInfo;
        }

        // Check for API not enabled
        if (stripos($basicInfo, 'API has not been used') !== false || stripos($basicInfo, 'not enabled') !== false) {
            return "⚠️ Google Cloud Vision API Not Enabled\n\n" .
                "The Vision API needs to be enabled in your Google Cloud project.\n\n" .
                "Enable it here: https://console.cloud.google.com/apis/library/vision.googleapis.com\n\n" .
                "Original error: " . $basicInfo;
        }

        // Check for authentication issues
        if (stripos($status, 'UNAUTHENTICATED') !== false || stripos($basicInfo, 'credentials') !== false) {
            return "⚠️ Authentication Error\n\n" .
                "There's an issue with your Google Cloud credentials.\n\n" .
                "Please check:\n" .
                "1. Credentials file exists: " . env('GOOGLE_APPLICATION_CREDENTIALS') . "\n" .
                "2. Credentials file is valid JSON\n" .
                "3. Service account has 'Cloud Vision API User' role\n\n" .
                "Original error: " . $basicInfo;
        }

        // Check for quota exceeded
        if (stripos($basicInfo, 'quota') !== false || stripos($basicInfo, 'RESOURCE_EXHAUSTED') !== false) {
            return "⚠️ API Quota Exceeded\n\n" .
                "Your Google Cloud Vision API quota has been exceeded.\n\n" .
                "Check your quota: https://console.cloud.google.com/apis/api/vision.googleapis.com/quotas\n\n" .
                "Original error: " . $basicInfo;
        }

        // Default error message
        return "Google Vision API Error: " . $basicInfo . " (Status: " . $status . ")";
    }

    public function __destruct()
    {
        if ($this->client) {
            $this->client->close();
        }
    }
}