<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the role/permission matrix from docs/09-crm-admin-architecture.md §1.
 * Clinic-scoped roles (owner/manager/staff) are further scoped per-clinic via
 * the clinic_user pivot + EnsureClinicMember middleware, not via Spatie teams.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Leads / CRM
            'leads.view', 'leads.assign', 'leads.manage',
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
            'patient' => [],
            'clinic_owner' => ['clinics.manage', 'doctors.manage', 'leads.view', 'billing.view'],
            'clinic_manager' => ['clinics.view', 'doctors.manage', 'leads.view'],
            'clinic_staff' => ['leads.view'],
            'doctor' => [],
            'sales_agent' => ['leads.view', 'leads.assign', 'leads.manage'],
            'content_editor' => ['content.view', 'content.edit'],
            'seo_manager' => ['seo.manage', 'content.view'],
            'moderator' => ['reviews.moderate', 'clinics.verify'],
            'finance' => ['billing.view', 'billing.manage', 'commissions.manage', 'invoices.manage'],
            'admin' => [
                'access-admin', 'leads.view', 'leads.assign', 'leads.manage',
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
    }
}
