<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    private const RESOURCES = [
        'pages' => ['table' => 'oc_information_description', 'label' => 'CMS pages', 'primary' => 'information_id', 'creatable' => false, 'fields' => ['title', 'description', 'meta_title', 'meta_description']],
        'members' => ['table' => 'oc_customer', 'label' => 'Members', 'primary' => 'customer_id', 'creatable' => false, 'fields' => ['firstname', 'lastname', 'email', 'telephone', 'mobile', 'designation', 'status']],
        'news' => ['table' => 'oc_news', 'label' => 'News', 'primary' => 'id', 'fields' => ['title', 'date', 'news']],
        'sliders' => ['table' => 'oc_slider', 'label' => 'Homepage sliders', 'primary' => 'id', 'fields' => ['title', 'image', 'sort', 'url']],
        'downloads' => ['table' => 'oc_downloads', 'label' => 'Downloads', 'primary' => 'id', 'fields' => ['title', 'upload', 'date_added']],
        'books' => ['table' => 'oc_books', 'label' => 'Books', 'primary' => 'id', 'fields' => ['title', 'subtitle', 'editors', 'year', 'price', 'isbn', 'image', 'about', 'contents']],
        'reports' => ['table' => 'oc_report', 'label' => 'Reports', 'primary' => 'id', 'fields' => ['title', 'image', 'upload']],
        'publication-abstracts' => ['table' => 'oc_abstract', 'label' => 'Publication abstracts', 'primary' => 'id', 'fields' => ['title', 'image', 'upload']],
        'awards' => ['table' => 'oc_awards', 'label' => 'Awards', 'primary' => 'id', 'fields' => ['name', 'type', 'year', 'guidlines', 'awardees', 'status']],
        'videos' => ['table' => 'oc_videos', 'label' => 'Videos', 'primary' => 'id', 'fields' => ['title', 'url', 'sort']],
        'gallery' => ['table' => 'oc_gallery', 'label' => 'Gallery', 'primary' => 'id', 'fields' => ['title', 'date']],
        'homepage-content' => ['table' => 'oc_newsletter_editors', 'label' => 'Homepage content', 'primary' => 'id', 'creatable' => false, 'fields' => ['notice']],
        'feedback' => ['table' => 'oc_feedback', 'label' => 'Feedback', 'primary' => 'id', 'fields' => ['name', 'email', 'contact', 'message']],
        'conferences' => ['table' => 'oc_conference', 'label' => 'Conferences', 'primary' => 'id', 'creatable' => false, 'fields' => ['title', 'subtitle', 'date', 'date_end', 'venue', 'type', 'status']],
    ];

    public function loginForm(): View|RedirectResponse
    {
        return session('admin_id') ? redirect()->route('admin.dashboard') : view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string']]);
        $user = DB::table('oc_user')->where('username', $data['username'])->where('status', 1)->first();
        $hash = $user ? sha1($user->salt . sha1($user->salt . sha1($data['password']))) : '';

        if (!$user || !hash_equals($user->password, $hash)) {
            return back()->withErrors(['username' => 'Invalid username or password.'])->onlyInput('username');
        }

        $request->session()->regenerate();
        session(['admin_id' => $user->user_id, 'admin_name' => $user->username]);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function dashboard(): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        return view('admin.dashboard', ['counts' => [
            'Members' => DB::table('oc_customer')->count(),
            'News items' => DB::table('oc_news')->count(),
            'Downloads' => DB::table('oc_downloads')->count(),
            'Conferences' => DB::table('oc_conference')->count(),
            'Feedback' => DB::table('oc_feedback')->count(),
            'Pending members' => DB::table('oc_customer')->where('status', 0)->count(),
        ]]);
    }

    public function index(string $resource): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        $config = $this->resource($resource);
        if ($resource === 'conferences') return view('admin.conference-index', ['items' => DB::table('oc_conference')->orderByDesc('id')->get()]);
        $query = DB::table($config['table'])->orderByDesc($config['primary']);
        if ($resource === 'pages') {
            $query->leftJoin('oc_information', 'oc_information.information_id', '=', 'oc_information_description.information_id')
                ->select('oc_information_description.*', 'oc_information.status');
            $query->where('language_id', 1);
        }
        return view('admin.index', ['resource' => $resource, 'config' => $config, 'items' => $query->get()]);
    }

    public function create(string $resource): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        if ($resource === 'conferences') return view('admin.conference-form', $this->conferenceFormData());
        if ($resource === 'gallery') return view('admin.gallery-form', ['gallery' => null, 'images' => collect()]);
        if ($this->isHomepageResource($resource)) {
            return view('admin.homepage-form', ['resource' => $resource, 'config' => $this->resource($resource), 'item' => null]);
        }
        return view('admin.form', ['resource' => $resource, 'config' => $this->resource($resource), 'item' => null]);
    }

    public function edit(string $resource, int $id): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        $config = $this->resource($resource);
        if ($resource === 'pages') {
            $page = DB::table('oc_information_description')->where('information_id', $id)->where('language_id', 1)->firstOrFail();
            return view('admin.page-form', [
                'page' => $page,
                'information' => DB::table('oc_information')->where('information_id', $id)->firstOrFail(),
                'seo' => DB::table('oc_seo_url')->where('store_id', 0)->where('language_id', 1)->where('query', 'information_id='.$id)->value('keyword') ?? '',
            ]);
        }
        if ($resource === 'members') {
            return view('admin.member-form', [
                'member' => DB::table('oc_customer')->where('customer_id', $id)->firstOrFail(),
                'countries' => DB::table('oc_country')->orderBy('name')->get(),
                'types' => DB::table('oc_customer_type')->orderBy('id')->get(),
                'zones' => ['OT' => 'None', 'CZ' => 'Central Zone', 'DZ' => 'Delhi Zone', 'EZ' => 'Eastern Zone', 'FM' => 'Foreign Zone', 'MEZ' => 'Mid-Eastern Zone', 'NEZ' => 'North-Eastern Zone', 'NZ' => 'Northern Zone', 'SZ' => 'Southern Zone', 'WZ' => 'Western Zone'],
                'states' => ['' => 'Others', 'Andaman and Nicobar Islands' => 'Andaman and Nicobar Islands', 'Andhra Pradesh' => 'Andhra Pradesh', 'Arunachal Pradesh' => 'Arunachal Pradesh', 'Assam' => 'Assam', 'Bihar' => 'Bihar', 'Chandigarh' => 'Chandigarh', 'Dadra and Nagar Haveli' => 'Dadra and Nagar Haveli', 'Daman and Diu' => 'Daman and Diu', 'Delhi' => 'Delhi', 'Goa' => 'Goa', 'Gujarat' => 'Gujarat', 'Haryana' => 'Haryana', 'Himachal Pradesh' => 'Himachal Pradesh', 'Jammu and Kashmir' => 'Jammu and Kashmir', 'Karnataka' => 'Karnataka', 'Kerala' => 'Kerala', 'Lakshadweep Islands' => 'Lakshadweep Islands', 'Madhya Pradesh' => 'Madhya Pradesh', 'Maharashtra' => 'Maharashtra', 'Manipur' => 'Manipur', 'Meghalaya' => 'Meghalaya', 'Mizoram' => 'Mizoram', 'Nagaland' => 'Nagaland', 'Odisha' => 'Odisha', 'Puducherry' => 'Puducherry', 'Punjab' => 'Punjab', 'Rajasthan' => 'Rajasthan', 'Sikkim' => 'Sikkim', 'Tamil Nadu' => 'Tamil Nadu', 'Tripura' => 'Tripura', 'Uttar Pradesh' => 'Uttar Pradesh', 'West Bengal' => 'West Bengal', 'Telangana' => 'Telangana', 'Chhattisgarh' => 'Chhattisgarh', 'Jharkhand' => 'Jharkhand', 'Uttarakhand' => 'Uttarakhand', 'Ladakh' => 'Ladakh'],
            ]);
        }
        if ($resource === 'conferences') return view('admin.conference-form', $this->conferenceFormData($id));
        if ($resource === 'gallery') return view('admin.gallery-form', ['gallery' => DB::table('oc_gallery')->where('id', $id)->firstOrFail(), 'images' => DB::table('oc_gallery_images')->where('gallery_id', $id)->orderBy('sort')->get()]);
        if ($this->isHomepageResource($resource)) {
            return view('admin.homepage-form', ['resource' => $resource, 'config' => $config, 'item' => DB::table($config['table'])->where($config['primary'], $id)->firstOrFail()]);
        }
        return view('admin.form', ['resource' => $resource, 'config' => $config, 'item' => DB::table($config['table'])->where($config['primary'], $id)->firstOrFail()]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        $config = $this->resource($resource);
        if ($resource === 'conferences') return $this->saveConference($request);
        if ($resource === 'gallery') return $this->saveGallery($request);
        if ($this->isHomepageResource($resource)) return $this->saveHomepageResource($request, $resource);
        DB::table($config['table'])->insert($this->payload($request, $config));
        return redirect()->route('admin.resource.index', $resource)->with('success', 'Saved.');
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        if ($resource === 'members') return $this->updateMember($request, $id);
        if ($resource === 'pages') return $this->updatePage($request, $id);
        if ($resource === 'conferences') return $this->saveConference($request, $id);
        if ($resource === 'gallery') return $this->saveGallery($request, $id);
        if ($this->isHomepageResource($resource)) return $this->saveHomepageResource($request, $resource, $id);
        $config = $this->resource($resource);
        DB::table($config['table'])->where($config['primary'], $id)->update($this->payload($request, $config));
        return redirect()->route('admin.resource.index', $resource)->with('success', 'Updated.');
    }

    private function updateMember(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'firstname' => ['required', 'string', 'max:32'], 'lastname' => ['required', 'string', 'max:32'], 'email' => ['required', 'email', 'max:96'], 'email2' => ['nullable', 'email', 'max:62'],
            'mobile' => ['nullable', 'string', 'max:32'], 'telephone' => ['nullable', 'string', 'max:32'], 'dob' => ['nullable', 'date'], 'add1' => ['nullable', 'string'], 'add2' => ['nullable', 'string'], 'add3' => ['nullable', 'string'], 'city' => ['nullable', 'string', 'max:255'], 'pincode' => ['nullable', 'string', 'max:255'], 'state' => ['nullable', 'string', 'max:255'], 'zone' => ['nullable', 'string', 'max:255'], 'country_id' => ['nullable', 'integer'], 'designation' => ['nullable', 'string', 'max:255'], 'specialization' => ['nullable', 'string', 'max:255'], 'allied' => ['nullable', 'string', 'max:255'], 'anydetail' => ['nullable', 'string', 'max:255'], 'fpsi' => ['nullable', 'string', 'max:255'], 'type' => ['nullable', 'integer'], 'total' => ['nullable', 'integer'], 'paid' => ['nullable', 'integer'], 'balance' => ['nullable', 'integer'], 'joining_date' => ['nullable', 'date'], 'code' => ['nullable', 'string', 'max:40'], 'enrollment_date' => ['nullable', 'date'], 'status' => ['required', 'integer'], 'safe' => ['required', 'integer'], 'headquarter' => ['required', 'integer'], 'password' => ['nullable', 'string', 'min:4', 'max:40', 'confirmed'],
        ]);
        unset($data['password_confirmation']);
        $password = $data['password'] ?? null;
        unset($data['password']);
        $data['email2'] ??= '';
        $data['country_id'] ??= 99;
        $data['mobile'] ??= '';
        $data['telephone'] ??= '';
        foreach (['dob', 'joining_date', 'enrollment_date'] as $field) $data[$field] = $data[$field] ?: null;
        if ($password) {
            $salt = Str::random(9);
            $data['salt'] = $salt;
            $data['password'] = sha1($salt . sha1($salt . sha1($password)));
        }
        DB::table('oc_customer')->where('customer_id', $id)->update($data);
        return redirect()->route('admin.resource.edit', ['resource' => 'members', 'id' => $id])->with('success', 'Member updated.');
    }

    private function updatePage(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:64'],
            'description' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'meta_keyword' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'seo_keyword' => ['nullable', 'regex:/^[a-z0-9-]*$/', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $id) {
            DB::table('oc_information_description')->where('information_id', $id)->where('language_id', 1)->update([
                'title' => $data['title'], 'description' => $data['description'], 'meta_title' => $data['meta_title'] ?? '',
                'meta_description' => $data['meta_description'] ?? '', 'meta_keyword' => $data['meta_keyword'] ?? '',
            ]);
            DB::table('oc_information')->where('information_id', $id)->update(['status' => $data['status']]);
            $query = 'information_id='.$id;
            $existing = DB::table('oc_seo_url')->where('store_id', 0)->where('language_id', 1)->where('query', $query);
            if (($data['seo_keyword'] ?? '') === '') $existing->delete();
            elseif ($existing->exists()) $existing->update(['keyword' => $data['seo_keyword']]);
            else DB::table('oc_seo_url')->insert(['store_id' => 0, 'language_id' => 1, 'query' => $query, 'keyword' => $data['seo_keyword']]);
        });

        return redirect()->route('admin.resource.edit', ['resource' => 'pages', 'id' => $id])->with('success', 'CMS page updated.');
    }

    private function conferenceFormData(?int $id = null): array
    {
        $conference = $id ? DB::table('oc_conference')->where('id', $id)->firstOrFail() : (object) [
            'id' => null, 'banner' => '', 'notice' => '', 'type' => 'national', 'suburl' => '', 'url' => '', 'title' => '', 'subtitle' => '',
            'date_text' => '', 'date' => '', 'date_end' => '', 'venue' => '', 'map' => '', 'year' => '', 'price' => 0,
            'invitation' => '', 'imp_dates' => '', 'sessions' => '', 'registrationstatus' => 0, 'abstractstatus' => 0, 'late_charges' => 0, 'status' => 0,
            'core_committee' => '', 'advisory_committee' => '', 'local_organisers' => '', 'co_organiser' => '', 'program' => '', 'announcement' => '',
            'reg_details' => '', 'abstract_details' => '', 'about_venue' => '', 'hotels' => '', 'tour' => '', 'visa' => '', 'sponsor' => '',
            'tomail' => '', 'ccmail' => '', 'ccmail2' => '', 'contact_us' => '', 'email_contact' => '',
        ];
        $relations = ['organiser' => 'oc_organisers', 'keynote' => 'oc_keynotes', 'pspeaker' => 'oc_plenery', 'local' => 'oc_local', 'national' => 'oc_national', 'international' => 'oc_international'];
        $people = [];
        foreach ($relations as $key => $table) $people[$key] = $id ? DB::table($table)->where('conference_id', $id)->get() : collect();
        return [
            'conference' => $conference, 'people' => $people,
            'payments' => $id ? DB::table('oc_con_payment')->where('conference_id', $id)->orderBy('sort')->get() : collect(),
            'sessions' => $id ? DB::table('oc_con_session')->where('conference_id', $id)->orderBy('sort')->get() : collect(),
        ];
    }

    private function saveConference(Request $request, ?int $id = null): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'subtitle' => ['nullable', 'string', 'max:255'], 'notice' => ['nullable', 'string'],
            'type' => ['required', 'in:national,international,zonal'], 'suburl' => ['nullable', 'string', 'max:45'], 'url' => ['nullable', 'string', 'max:255'],
            'date_text' => ['nullable', 'string', 'max:255'], 'date' => ['nullable', 'string', 'max:255'], 'date_end' => ['nullable', 'string', 'max:255'],
            'venue' => ['nullable', 'string'], 'map' => ['nullable', 'string'], 'year' => ['nullable', 'string', 'max:255'], 'price' => ['nullable', 'numeric'],
            'banner' => ['nullable', 'string', 'max:255'], 'status' => ['required', 'boolean'], 'registrationstatus' => ['required', 'boolean'], 'abstractstatus' => ['required', 'boolean'], 'late_charges' => ['required', 'boolean'],
        ]);
        $textFields = ['invitation', 'imp_dates', 'sessions', 'core_committee', 'advisory_committee', 'local_organisers', 'co_organiser', 'program', 'announcement', 'reg_details', 'abstract_details', 'about_venue', 'hotels', 'tour', 'visa', 'sponsor', 'contact_us', 'email_contact'];
        foreach ($textFields as $field) $data[$field] = $request->input($field, '');
        foreach (['tomail', 'ccmail', 'ccmail2'] as $field) $data[$field] = $request->input($field, '');
        $data['url'] = in_array($data['type'], ['national', 'international'], true) ? $data['type'] : ($data['url'] ?? '');
        $data['price'] ??= 0;

        $conferenceId = DB::transaction(function () use ($data, $request, $id) {
            if ($id) {
                DB::table('oc_conference')->where('id', $id)->update($data + ['date_added' => now()]);
                $conferenceId = $id;
            } else {
                $conferenceId = DB::table('oc_conference')->insertGetId($data + ['date_added' => now()]);
            }
            $tables = ['organiser' => 'oc_organisers', 'keynote' => 'oc_keynotes', 'pspeaker' => 'oc_plenery', 'local' => 'oc_local', 'national' => 'oc_national', 'international' => 'oc_international'];
            foreach ($tables as $input => $table) {
                DB::table($table)->where('conference_id', $conferenceId)->delete();
                foreach ($request->input($input, []) as $person) {
                    if (blank($person['name'] ?? null)) continue;
                    DB::table($table)->insert(['conference_id' => $conferenceId, 'name' => $person['name'], 'image' => $person['image'] ?? '', 'about' => $person['about'] ?? '']);
                }
            }
            DB::table('oc_con_payment')->where('conference_id', $conferenceId)->delete();
            foreach ($request->input('payment', []) as $payment) {
                if (blank($payment['title'] ?? null)) continue;
                DB::table('oc_con_payment')->insert(['conference_id' => $conferenceId, 'title' => $payment['title'], 'amount' => $payment['amount'] ?? 0, 'lateamount' => $payment['lateamount'] ?? 0, 'accompany' => $payment['accompany'] ?? 0, 'currency' => $payment['currency'] ?? 'INR', 'sort' => $payment['sort'] ?? 0]);
            }
            DB::table('oc_con_session')->where('conference_id', $conferenceId)->delete();
            foreach ($request->input('session', []) as $session) {
                if (blank($session['title'] ?? null)) continue;
                DB::table('oc_con_session')->insert(['conference_id' => $conferenceId, 'title' => $session['title'], 'sort' => $session['sort'] ?? 0]);
            }
            return $conferenceId;
        });
        return redirect()->route('admin.resource.edit', ['resource' => 'conferences', 'id' => $conferenceId])->with('success', 'Conference updated locally.');
    }

    private function saveGallery(Request $request, ?int $id = null): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'date' => ['required', 'date'], 'gallery_image' => ['nullable', 'array']]);
        $galleryId = DB::transaction(function () use ($data, $request, $id) {
            $galleryId = $id ?: DB::table('oc_gallery')->insertGetId(['title' => $data['title'], 'date' => $data['date']]);
            if ($id) DB::table('oc_gallery')->where('id', $id)->update(['title' => $data['title'], 'date' => $data['date']]);
            DB::table('oc_gallery_images')->where('gallery_id', $galleryId)->delete();
            foreach ($request->input('gallery_image', []) as $image) if (filled($image['image'] ?? null)) DB::table('oc_gallery_images')->insert(['gallery_id' => $galleryId, 'image' => $image['image'], 'sort' => $image['sort_order'] ?? 0]);
            return $galleryId;
        });
        return redirect()->route('admin.resource.edit', ['resource' => 'gallery', 'id' => $galleryId])->with('success', 'Gallery updated locally.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        $config = $this->resource($resource);
        DB::table($config['table'])->where($config['primary'], $id)->delete();
        return redirect()->route('admin.resource.index', $resource)->with('success', 'Deleted.');
    }

    public function conferenceRegistrations(int $id): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        $conference = DB::table('oc_conference')->where('id', $id)->firstOrFail();
        $records = DB::table('users as u')->leftJoin('oc_conf_accomodation as a', 'a.conf_user_id', '=', 'u.id')->where('u.conf_id', $id)->latest('u.id')->select('u.*', 'a.hotel', 'a.accomodation', 'a.start', 'a.end', 'a.status as accommodation_status')->get();
        return view('admin.conference-registrations', compact('conference', 'records'));
    }

    public function conferenceAbstracts(int $id): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        $conference = DB::table('oc_conference')->where('id', $id)->firstOrFail();
        $records = DB::table('absubmissions as ab')->leftJoin('users as u', 'u.id', '=', 'ab.user_id')->where('ab.conf_id', $id)->latest('ab.id')->select('ab.*', 'ab.id as abstract_id', 'u.name', 'u.lname', 'u.title as user_title', 'u.department', 'u.email', 'u.address', 'u.city', 'u.state', 'u.country', 'u.pincode', 'u.payment_status')->get();
        return view('admin.conference-abstracts', compact('conference', 'records'));
    }

    public function conferenceRegistrant(int $id): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        $user = DB::table('users')->where('id', $id)->firstOrFail();
        return view('admin.conference-registrant', ['user' => $user, 'conference' => DB::table('oc_conference')->where('id', $user->conf_id)->firstOrFail(), 'abstracts' => DB::table('absubmissions')->where('user_id', $id)->get(), 'payment' => DB::table('payments')->where('user', $id)->latest('id')->first(), 'accommodation' => DB::table('oc_conf_accomodation')->where('conf_user_id', $id)->first(), 'passport' => DB::table('oc_conf_passport')->where('conf_user_id', $id)->first()]);
    }

    public function conferenceAbstractPreview(int $id): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        $user = DB::table('users')->where('id', $id)->firstOrFail();
        return view('admin.conference-abstract-preview', ['user' => $user, 'abstracts' => DB::table('absubmissions')->where('user_id', $id)->get()]);
    }

    public function conferenceDownload(int $id)
    {
        if ($redirect = $this->redirectIfGuest()) return $redirect;
        $conference = DB::table('oc_conference')->where('id', $id)->firstOrFail();
        $rows = DB::table('absubmissions')->where('conf_id', $id)->orderBy('id')->get(['id', 'title', 'authors', 'presentation', 'session', 'status']);
        return response()->streamDownload(function () use ($rows) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['ID', 'Title', 'Authors', 'Presentation', 'Session', 'Status']);
            foreach ($rows as $row) fputcsv($stream, (array) $row);
            fclose($stream);
        }, 'conference-' . $conference->id . '-abstracts.csv', ['Content-Type' => 'text/csv']);
    }

    private function saveHomepageResource(Request $request, string $resource, ?int $id = null): RedirectResponse
    {
        $config = $this->resource($resource);
        $rules = match ($resource) {
            'sliders' => ['title' => ['required', 'string', 'max:255'], 'sort' => ['nullable', 'integer'], 'url' => ['required', 'string', 'max:255'], 'image_file' => [$id ? 'nullable' : 'required', 'image', 'max:10240']],
            'news' => ['title' => ['required', 'string', 'max:255'], 'date' => ['required', 'date'], 'news' => ['nullable', 'string']],
            'downloads' => ['title' => ['required', 'string', 'max:255'], 'upload_file' => [$id ? 'nullable' : 'required', 'file', 'max:20480']],
            'homepage-content' => ['notice' => ['nullable', 'string']],
        };
        $data = $request->validate($rules);

        if ($resource === 'sliders') {
            $data = ['title' => $data['title'], 'sort' => $data['sort'] ?? 0, 'url' => $data['url'], 'homepage' => $request->boolean('homepage') ? 1 : 0];
            if ($request->hasFile('image_file')) $data['image'] = $this->storeCatalogUpload($request, 'image_file');
        } elseif ($resource === 'news') {
            $data = ['title' => $data['title'], 'date' => $data['date'], 'news' => $data['news'] ?? ''];
            if (!$id) $data['date_added'] = now();
        } elseif ($resource === 'downloads') {
            $data = ['title' => $data['title']];
            if ($request->hasFile('upload_file')) $data['upload'] = $this->storeCatalogUpload($request, 'upload_file');
            if (!$id) $data['date_added'] = now();
        } else {
            $data = ['notice' => $data['notice'] ?? ''];
        }

        if ($id) DB::table($config['table'])->where($config['primary'], $id)->update($data);
        else DB::table($config['table'])->insert($data);
        return redirect()->route('admin.resource.index', $resource)->with('success', $id ? 'Updated.' : 'Saved.');
    }

    private function storeCatalogUpload(Request $request, string $field): string
    {
        $file = $request->file($field);
        $directory = public_path('image/catalog');
        if (!is_dir($directory)) mkdir($directory, 0755, true);
        $filename = now()->format('YmdHis') . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);
        return 'catalog/' . $filename;
    }

    private function isHomepageResource(string $resource): bool
    {
        return in_array($resource, ['sliders', 'news', 'downloads', 'homepage-content'], true);
    }

    private function payload(Request $request, array $config): array
    {
        $data = $request->only($config['fields']);
        foreach ($data as $key => $value) $data[$key] = $value ?? '';
        if ($config['table'] === 'oc_news') $data['date_added'] = now();
        if (in_array($config['table'], ['oc_books', 'oc_report', 'oc_abstract'], true)) { $data['user_id'] = session('admin_id', 1); $data['date_added'] = now(); }
        if ($config['table'] === 'oc_feedback') $data['date_added'] = now();
        if ($config['table'] === 'oc_slider') $data['homepage'] = 0;
        if ($config['table'] === 'oc_conference') $data['date_added'] = now();
        if ($config['table'] === 'oc_information_description') $data['language_id'] = 1;
        return $data;
    }

    private function resource(string $resource): array { abort_unless(isset(self::RESOURCES[$resource]), 404); return self::RESOURCES[$resource]; }
    private function redirectIfGuest(): ?RedirectResponse { return session('admin_id') ? null : redirect()->route('admin.login'); }
}
