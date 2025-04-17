<?php

namespace ExpertShipping\Spl\Services;

use OpenAI\Client;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = \OpenAI::factory()
            ->withApiKey(config('openai.api_key'))
            ->withOrganization(config('openai.organization'))
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 120]))
            ->make();
    }

    public function generateCarrierService(string $keyword, string $language): array
    {
        // Define the list of services.
        $servicesList = "Service Shipping: Offer a wide range of national and international shipping services; "
            . "Box Posts: Forwarding subscriptions and services; "
            . "Insurance: Offered insurance for deposits and shipments; "
            . "Packaging services and supplies: Specialized packaging and supplies, custom packaging; "
            . "Collection and deposit services (PUDO): Online order collection and free return services for clients; "
            . "Printing and photocopy services: Professional and personal printing solutions.";

        // Build the prompt based on the selected language.
        $prompt = match ($language) {
            'fr' => "Générez un JSON avec les clés suivantes : 'store_description', 'title_seo', 'h1', 'description_seo'. Pour le magasin \"$keyword\", intégrez les services suivants : $servicesList. La valeur de 'store_description' doit être un paragraphe détaillé et engageant décrivant le magasin. 'title_seo' doit être un titre optimisé pour le SEO contenant entre 20 et 60 caractères. 'h1' doit être un titre principal (H1) pertinent. 'description_seo' doit être une méta description optimisée pour le SEO contenant entre 150 et 220 caractères. Le contenu doit être en français.",
            'en' => "Generate a JSON with the following keys: 'store_description', 'title_seo', 'h1', 'description_seo'. For the store \"$keyword\", incorporate the following services: $servicesList. The 'store_description' should be a detailed, engaging paragraph describing the store. 'title_seo' should be an SEO-optimized title between 20 and 60 characters. 'h1' should be a relevant H1 header. 'description_seo' should be an SEO-optimized meta description between 150 and 220 characters. The content should be in English.",
            default => ""
        };

        if (empty($prompt)) {
            return [];
        }

        $response = $this->client->chat()->create([
            'model' => 'gpt-4',
            "messages" => [
                [
                    "role"    => "system",
                    "content" => "You are an expert SEO content generator specializing in shipping and logistics."
                ],
                [
                    "role"    => "user",
                    "content" => $prompt
                ]
            ],
        ]);

        $content = $response['choices'][0]['message']['content'] ?? '';
        $content = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON response: " . json_last_error_msg());
        }

        return $content;
    }

    public function generateCompanyFAQs(string $companyName, string $language = 'en'): array
    {
        // Définir le nom de la langue pour le prompt.
        $languageName = $language === 'en' ? 'English' : ($language === 'fr' ? 'French' : ucfirst($language));

        // Liste fixe des FAQ de base.
        $fixedFaqs = [
            [
                'question' => 'What services we do offer?',
                'answer'   => 'national and international shipping services, PO boxes, shipping Insurance, Dropoff, Printing and photocopy services',
            ],
            [
                'question' => 'How can I contact Expert Shipping customer support?',
                'answer'   => 'by email at "Expert Shipping" <customerservice@expertshipping.ca>',
            ],
            [
                'question' => 'Does Expert Shipping offer international shipping?',
                'answer'   => 'YES',
            ],
            [
                'question' => 'What payment methods Expert Shipping accept?',
                'answer'   => 'Card/CASH/INTERAC',
            ],
            [
                'question' => 'How can I track my shipment?',
                'answer'   => 'on our website tracking.expertshipping.ca',
            ],
            [
                'question' => 'Puis-je expédier des médicaments',
                'answer'   => 'possible for some destinations and no for other',
            ],
            [
                'question' => 'Can I get a quote for shipping ?',
                'answer'   => 'Yes you can get quote on store and on our mobile app',
            ],
            [
                'question' => 'What is the cost of shipping',
                'answer'   => 'depends on destination and packages',
            ],
            [
                'question' => 'Combien de temps faudra-t-il pour la livraison?',
                'answer'   => 'depends on destination',
            ],
            [
                'question' => 'Is there a loyalty program at Expert Shipping, and how can I join?',
                'answer'   => 'by downloading our mobile app and using the customer id on it for your store purchases',
            ],
            [
                'question' => 'How can I apply for a job at a Expert Shipping store?',
                'answer'   => 'by mail at customerservice@expertshipping.ca',
            ],
            [
                'question' => 'Do all Expert Shipping stores accept Amazon Returns?',
                'answer'   => 'YES',
            ],
        ];

        // Encoder la liste fixe en JSON pour l'intégrer dans le prompt.
        $fixedFaqJson = json_encode($fixedFaqs, JSON_PRETTY_PRINT);

        // Construire un prompt pour générer un contenu unique et SEO optimisé.
        $prompt  = "Below is a fixed list of FAQ items for Expert Shipping:\n";
        $prompt .= "$fixedFaqJson\n\n";
        $prompt .= "For the store named \"$companyName\", generate unique, SEO-optimized versions of these FAQs. ";
        $prompt .= "Rephrase each question and answer to create unique, engaging content tailored for the shipping industry while preserving the original meaning. ";
        $prompt .= "Each answer must be significantly more detailed and longer, providing additional context and information useful for customers. ";
        $prompt .= "The content should be in $languageName. ";
        $prompt .= "Return the output in JSON format as an array of objects, each with the keys \"question\" and \"answer\".";

        // Préparer les messages pour l'API.
        $messages = [
            [
                "role"    => "system",
                "content" => "You are an expert SEO content generator specializing in shipping and logistics."
            ],
            [
                "role"    => "user",
                "content" => $prompt
            ]
        ];

        $response = $this->client->chat()->create([
            'model'       => 'gpt-4',
            'messages'    => $messages,
            'temperature' => 0.7,
            'max_tokens'  => 2000,
        ]);

        // Extraire et décoder la réponse.
        $faqContent = $response['choices'][0]['message']['content'] ?? '';
        $faqs = json_decode($faqContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON response: " . json_last_error_msg());
        }

        return $faqs;
    }
}
