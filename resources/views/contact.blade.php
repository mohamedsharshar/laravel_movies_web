@extends('layouts.app')

@section('content')
<div class="contact-container">
    <div class="contact-card">
        <h1>Contact Us</h1>
        <p class="subtitle">We'd love to hear from you! Please fill out the form below and our team will get back to you as soon as possible.</p>
        <form class="contact-form" method="POST" action="{{ route('contact.send') }}">
            @csrf
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Your Name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Your Email" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" placeholder="Your Message" required></textarea>
            </div>
            <button type="submit" class="contact-btn">Send Message</button>
        </form>
        @if(session('success'))
            <div style="margin-top:18px;color:#00bfae;font-weight:bold;text-align:center;">{{ session('success') }}</div>
        @endif
    </div>
</div>
<style>
.contact-container {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-page-bg);
    padding: 2rem 0;
}
.contact-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(28,37,65,0.10);
    padding: 2.5rem 2.2rem 2rem 2.2rem;
    max-width: 420px;
    width: 100%;
    margin: 0 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.contact-card h1 {
    font-size: 2rem;
    color: var(--color-sidebar-bg);
    margin-bottom: 0.5rem;
    font-weight: bold;
    letter-spacing: 1px;
}
.contact-card .subtitle {
    color: #555;
    font-size: 1.08rem;
    margin-bottom: 1.5rem;
    text-align: center;
}
.contact-form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.form-group label {
    font-weight: 500;
    color: #1C2541;
    margin-bottom: 2px;
}
.form-group input,
.form-group textarea {
    border: 1.5px solid #d1f7f2;
    border-radius: 8px;
    padding: 0.7rem 1rem;
    font-size: 1.05rem;
    background: #f8fafc;
    color: #1C2541;
    transition: border 0.18s;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--color-accent);
    outline: none;
}
.contact-btn {
    background: var(--color-accent);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.8rem 1.5rem;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,209,178,0.10);
    transition: background 0.18s, box-shadow 0.18s;
    margin-top: 0.5rem;
}
.contact-btn:hover {
    background: #00bfae;
    box-shadow: 0 4px 16px rgba(0,209,178,0.18);
}
@media (max-width: 600px) {
    .contact-card {
        padding: 1.2rem 0.7rem 1.2rem 0.7rem;
        max-width: 98vw;
    }
    .contact-card h1 {
        font-size: 1.3rem;
    }
    .contact-form {
        gap: 0.7rem;
    }
}
</style>
@endsection
