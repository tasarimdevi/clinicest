<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\City;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * See docs/09-crm-admin-architecture.md §3: "apply -> upload license/
 * credentials/ISO docs -> admin verification workflow -> profile build ->
 * choose subscription -> go live." This is the "apply" step plus the
 * account it needs — the first public account-creation path in this app
 * (routes/web/auth.php's own docblock notes registration wasn't wired
 * yet). Scoped narrowly to clinic onboarding, not a general registration
 * system: it always creates a new User with the clinic_owner role, it
 * doesn't let an already-authenticated user attach a second clinic.
 *
 * "Subscription" is cut (needs Stripe/iyzico, same as commissions/
 * invoicing elsewhere). "Upload" is a pasted link, not a real file
 * upload — no upload subsystem exists anywhere else in this admin either.
 */
#[Layout('layouts.public')]
class ClinicApplicationPage extends Component
{
    public string $clinic_name = '';

    public ?int $city_id = null;

    public string $phone = '';

    public string $whatsapp = '';

    public string $email = '';

    public string $website = '';

    public string $about = '';

    public string $credentials_url = '';

    public string $application_message = '';

    public string $owner_name = '';

    public string $owner_email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'clinic_name' => ['required', 'string', 'max:255'],
            'city_id' => ['required', Rule::exists(City::class, 'id')],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'about' => ['nullable', 'string', 'max:2000'],
            'credentials_url' => ['nullable', 'url', 'max:2048'],
            'application_message' => ['nullable', 'string', 'max:2000'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function submit(): void
    {
        $throttleKey = 'clinic-apply:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('clinic_name', __('Too many attempts. Please try again later.'));

            return;
        }

        $validated = $this->validate();
        RateLimiter::hit($throttleKey, 3600);

        $clinic = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => $validated['password'],
            ]);
            $user->assignRole('clinic_owner');

            $clinic = Clinic::create([
                'slug' => $this->uniqueSlug($validated['clinic_name']),
                'name' => $validated['clinic_name'],
                'city_id' => $validated['city_id'],
                'phone' => $validated['phone'] ?: null,
                'whatsapp' => $validated['whatsapp'] ?: null,
                'email' => $validated['email'] ?: null,
                'website' => $validated['website'] ?: null,
                'about' => $validated['about'] ?: null,
                'owner_user_id' => $user->id,
                'verification_tier' => 'pending',
                'is_active' => false,
                'application_status' => 'pending',
                'application_message' => $validated['application_message'] ?: null,
                'credentials_url' => $validated['credentials_url'] ?: null,
                'applied_at' => now(),
            ]);

            $clinic->users()->attach($user->id, ['role' => 'owner', 'invited_at' => now()]);

            Auth::login($user);

            return $clinic;
        });

        RateLimiter::clear($throttleKey);

        $this->redirect(route('clinic.dashboard', $clinic), navigate: false);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Clinic::where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.public.clinic-application-page', [
            'cities' => City::orderBy('name')->get(),
        ]);
    }
}
