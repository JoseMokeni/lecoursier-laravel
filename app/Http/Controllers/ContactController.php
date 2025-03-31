<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string',
            'message' => 'required|min:10|max:1000',
            'privacy' => 'required',
        ]);

        // Remove privacy field as we don't need it in the email
        unset($validated['privacy']);

        try {
            // Send email
            Mail::to(config('mail.contact_address', 'contact@lecoursier.app'))
                ->send(new ContactFormMail($validated));

            return redirect()
                ->back()
                ->with('success', 'Votre message a été envoyé. Nous vous répondrons dans les plus brefs délais.');
        } catch (\Exception $e) {
            Log::error('Failed to send contact email: ' . $e->getMessage());

            return response()->view('errors.500', [
                'message' => 'Impossible d\'envoyer votre message. Veuillez réessayer plus tard.'
            ], 500);
        }
    }
}
