<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AboutUsController extends Controller
{
    public function index(): View
    {
        return view('about-us', ['sponsors' => Sponsor::all()]);
    }

    public function sendMail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email'],
            'subject' => ['required', 'min:3'],
            'message' => ['required', 'min:5'],
        ]);

        Mail::raw("Message from {$data['name']} <{$data['email']}>:\n\n{$data['message']}", function ($mail) use ($data): void {
            $mail->to(config('services.contact.to'))
                ->replyTo($data['email'], $data['name'])
                ->subject($data['subject']);
        });

        return redirect('/aboutus')->with('success', 'Your message has been sent successfully!');
    }
}
