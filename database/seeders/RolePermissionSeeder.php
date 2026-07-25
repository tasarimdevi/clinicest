<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the role/permission matrix from docs/09-crm-admin-architecture.md §1.
 * Clinic-scoped roles (owner/manager/staff) are further scoped per-clinic via
 * the clinic_user pivot + EnsureClinicMember middleware, not via Spatie teams.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Spatie caches the role<->permission map. Re-running this seeder
        // (every test via RefreshDatabase re-creates roles/permissions with
        // reused auto-increment IDs) can otherwise leave a stale cache
        // pointing old IDs at the wrong role, silently granting or denying
        // the wrong permissions.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Leads / CRM
            'leads.view', 'leads.assign', 'leads.manage',
            // Offers
            'offers.view', 'offers.manage',
            // Clinics
            'clinics.view', 'clinics.manage', 'clinics.verify',
            // Doctors
            'doctors.view', 'doctors.manage',
            // Content
            'content.view', 'content.edit', 'content.publish',
            // Reviews
            'reviews.moderate',
            // SEO
            'seo.manage',
            // Billing
            'billing.view', 'billing.manage', 'commissions.manage', 'invoices.manage',
            // Platform admin
            'users.manage', 'roles.manage', 'settings.manage', 'access-admin',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $roles = [
            // Clinic-portal roles never get access-admin — they operate
            // entirely within /clinic/{id} via EnsureClinicMember, not /admin.
            'patient' => [],
            'clinic_owner' => ['clinics.manage', 'doctors.manage', 'leads.view', 'offers.view', 'offers.manage', 'billing.view'],
            'clinic_manager' => ['clinics.view', 'doctors.manage', 'leads.view', 'offers.view', 'offers.manage'],
            'clinic_staff' => ['leads.view'],
            'doctor' => [],
            // Internal staff roles all need access-admin just to reach
            // /admin at all; their other permissions then scope what they
            // see once inside (docs/09-crm-admin-architecture.md §1).
            'sales_agent' => ['access-admin', 'leads.view', 'leads.assign', 'leads.manage', 'offers.view'],
            'content_editor' => ['access-admin', 'content.view', 'content.edit'],
            'seo_manager' => ['access-admin', 'seo.manage', 'content.view'],
            'moderator' => ['access-admin', 'reviews.moderate', 'clinics.verify'],
            'finance' => ['access-admin', 'billing.view', 'billing.manage', 'commissions.manage', 'invoices.manage'],
            'admin' => [
                'access-admin', 'leads.view', 'leads.assign', 'leads.manage',
                'offers.view', 'offers.manage',
                'clinics.view', 'clinics.manage', 'clinics.verify',
                'doctors.view', 'doctors.manage',
                'content.view', 'content.edit', 'content.publish',
                'reviews.moderate', 'seo.manage',
                'billing.view', 'billing.manage', 'commissions.manage', 'invoices.manage',
                'users.manage',
            ],
            'super_admin' => $permissions, // gets everything, including roles.manage/settings.manage
        ];

        foreach ($roles as $role => $rolePermissions) {
            Role::findOrCreate($role)->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
