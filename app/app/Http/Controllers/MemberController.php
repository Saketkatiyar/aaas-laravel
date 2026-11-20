<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function loginForm(): View|RedirectResponse { return session('member_id') ? redirect('/account') : view('member.login'); }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $member = DB::table('oc_customer')->where('email', $data['email'])->where('status', 1)->first();
        $hash = $member ? sha1($member->salt . sha1($member->salt . sha1($data['password']))) : '';
        if (!$member || !hash_equals($member->password, $hash)) return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        $request->session()->regenerate();
        session(['member_id' => $member->customer_id, 'member_name' => trim($member->firstname . ' ' . $member->lastname)]);
        return redirect('/account');
    }

    public function registerForm(): View { return view('member.register'); }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate(['firstname' => ['required','string','max:32'], 'lastname' => ['nullable','string','max:32'], 'email' => ['required','email','max:96'], 'telephone' => ['required','string','max:32'], 'password' => ['required','string','min:4','max:40','confirmed']]);
        if (DB::table('oc_customer')->where('email', $data['email'])->exists()) return back()->withErrors(['email' => 'A member already uses this email address.'])->withInput();
        $salt = Str::random(9);
        $id = DB::table('oc_customer')->insertGetId([
            'customer_group_id' => 1, 'store_id' => 0, 'language_id' => 1, 'firstname' => $data['firstname'], 'lastname' => $data['lastname'] ?? '', 'email' => $data['email'], 'email2' => '', 'telephone' => $data['telephone'], 'mobile' => $data['telephone'], 'fax' => '', 'password' => sha1($salt . sha1($salt . sha1($data['password']))), 'salt' => $salt, 'cart' => '', 'wishlist' => '', 'newsletter' => 0, 'address_id' => 0, 'custom_field' => '', 'ip' => $request->ip(), 'status' => 0, 'safe' => 0, 'token' => '', 'code' => '', 'type' => 1, 'date_added' => now(), 'country_id' => 99, 'headquarter' => 0,
        ]);
        session(['member_id' => $id, 'member_name' => trim($data['firstname'] . ' ' . ($data['lastname'] ?? ''))]);
        return redirect('/account')->with('success', 'Registration received. Your membership is pending approval.');
    }

    public function account(): View|RedirectResponse
    {
        if (!session('member_id')) return redirect('/login');
        return view('member.account', ['member' => DB::table('oc_customer')->where('customer_id', session('member_id'))->firstOrFail()]);
    }

    public function logout(Request $request): RedirectResponse { $request->session()->forget(['member_id', 'member_name']); return redirect('/'); }

    public function feedbackForm(): View { return view('public.feedback'); }

    public function feedback(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required','string','max:255'], 'email' => ['required','email','max:255'], 'contact' => ['nullable','string','max:255'], 'message' => ['required','string']]);
        DB::table('oc_feedback')->insert([...$data, 'date_added' => now()]);
        return back()->with('success', 'Thanks for submitting your feedback.');
    }
}
