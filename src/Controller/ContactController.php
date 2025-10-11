<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact/submit', name: 'contact_form_submit', methods: ['POST'])]
    public function submitContactForm(
        Request $request,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): JsonResponse
    {
        try {
            // Pobierz dane z formularza
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $phone = $request->request->get('phone');
            $company = $request->request->get('company');
            $subject = $request->request->get('subject');
            $message = $request->request->get('message');
            $recipient = $request->request->get('recipient');
            $locale = $request->request->get('locale', 'pl');

            // Walidacja podstawowa
            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Wszystkie wymagane pola muszą być wypełnione.'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Walidacja email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Nieprawidłowy adres e-mail.'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Przygotuj treść emaila
            $emailBody = $this->renderView('emails/contact.html.twig', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'company' => $company,
                'subject' => $subject,
                'message' => $message,
                'locale' => $locale
            ]);

            // Utwórz email
            $emailMessage = (new Email())
                ->from($email)
                ->to($recipient ?: $_ENV['SULU_ADMIN_EMAIL'])
                ->replyTo($email)
                ->subject('[Part-ner.pl] ' . $subject)
                ->html($emailBody);

            // Wyślij email
            $mailer->send($emailMessage);

            // Opcjonalne: Wyślij potwierdzenie do nadawcy
            $confirmationEmail = (new Email())
                ->from($_ENV['SULU_ADMIN_EMAIL'])
                ->to($email)
                ->subject($this->getConfirmationSubject($locale))
                ->html($this->renderView('emails/confirmation.html.twig', [
                    'name' => $name,
                    'locale' => $locale
                ]));

            $mailer->send($confirmationEmail);

            $logger->info('Contact form submitted', [
                'email' => $email,
                'subject' => $subject
            ]);

            return new JsonResponse([
                'success' => true,
                'message' => 'Wiadomość została wysłana pomyślnie.'
            ]);

        } catch (\Exception $e) {
            $logger->error('Contact form error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Wystąpił błąd podczas wysyłania wiadomości.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getConfirmationSubject(string $locale): string
    {
        $subjects = [
            'pl' => 'Dziękujemy za kontakt - Part-ner.pl',
            'en' => 'Thank you for contacting us - Part-ner.pl',
            'de' => 'Vielen Dank für Ihre Kontaktaufnahme - Part-ner.pl'
        ];

        return $subjects[$locale] ?? $subjects['pl'];
    }
}
