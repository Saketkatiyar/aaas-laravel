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
        $member = DB::table('oc_customer')->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])->first();
        if ($member && !$member->status) return back()->withErrors(['email' => 'Your account requires approval before you can sign in.'])->onlyInput('email');
        $hash = $member ? sha1($member->salt . sha1($member->salt . sha1($data['password']))) : '';
        $legacyHash = md5($data['password']);
        if (!$member || !hash_equals($member->password, $hash) && !hash_equals($member->password, $legacyHash)) return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
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
        return redirect('/account/profile');
    }

    public function profile(): View|RedirectResponse
    {
        if (!session('member_id')) return redirect('/login');
        $member = DB::table('oc_customer')->where('customer_id', session('member_id'))->firstOrFail();
        return view('member.account', ['member' => $member, 'membership' => DB::table('oc_customer_type')->where('id', $member->type)->first(), 'states' => DB::table('oc_zone')->where('country_id', 99)->orderBy('name')->get(), 'zones' => DB::table('oc_zonal')->orderBy('id')->get()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        if (!session('member_id')) return redirect('/login');
        $member = DB::table('oc_customer')->where('customer_id', session('member_id'))->firstOrFail();
        $data = $request->validate(['firstname'=>['required','string','max:32'],'lastname'=>['required','string','max:32'],'email'=>['required','email','max:96'],'email2'=>['nullable','email','max:62'],'telephone'=>['required','string','min:3','max:32'],'mobile'=>['nullable','string','max:32'],'designation'=>['nullable','string','max:255'],'fpsi'=>['nullable','string','max:255'],'specialization'=>['nullable','string','max:255'],'allied'=>['nullable','string','max:255'],'anydetail'=>['nullable','string','max:255'],'add1'=>['nullable','string'],'add2'=>['nullable','string'],'add3'=>['nullable','string'],'pincode'=>['nullable','string','max:255'],'city'=>['nullable','string','max:255'],'state'=>['nullable','string','max:255'],'zone'=>['nullable','string','max:255']]);
        if (strcasecmp($member->email, $data['email']) !== 0 && DB::table('oc_customer')->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])->exists()) return back()->withErrors(['email'=>'A member already uses this email address.'])->withInput();
        DB::table('oc_customer')->where('customer_id', $member->customer_id)->update($data);
        session(['member_name'=>trim($data['firstname'].' '.$data['lastname'])]);
        return redirect('/account/profile')->with('success','Your account details have been updated.');
    }

    public function passwordForm(): View|RedirectResponse { return session('member_id') ? view('member.password') : redirect('/login'); }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (!session('member_id')) return redirect('/login');
        $data = $request->validate(['password'=>['required','string','min:4','max:40','confirmed']]);
        $salt = Str::random(9);
        DB::table('oc_customer')->where('customer_id', session('member_id'))->update(['salt'=>$salt,'password'=>sha1($salt.sha1($salt.sha1($data['password']))),'code'=>'']);
        return redirect('/account/profile')->with('success','Your password has been updated.');
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
