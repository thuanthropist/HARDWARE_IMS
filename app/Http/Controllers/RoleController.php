<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Seeded at install time and relied on by hardcoded `User::role([...])`
     * checks across the app (notification recipients, dispatch guards) —
     * deleting one of these would silently break those checks rather than
     * erroring, so they're excluded from deletion regardless of whether
     * any user currently holds them.
     */
    private const PROTECTED_ROLES = ['Admin', 'Manager', 'Warehouse Staff', 'Viewer'];

    /**
     * @var array<string, array<int, string>>
     */
    private const PERMISSION_GROUPS = [
        'Catalog & Inventory' => ['manage-products', 'manage-stock', 'manage-stock-adjustments', 'manage-warehouses', 'manage-suppliers', 'manage-departments'],
        'Planning Tools' => ['manage-calculators'],
        'Sales & Fulfillment' => ['manage-orders', 'manage-quotes', 'manage-pos', 'apply-pos-discount'],
        'Reporting' => ['view-reports'],
        'Administration' => ['manage-users', 'manage-roles', 'manage-license', 'manage-settings'],
    ];

    public function create(): View
    {
        return view('roles.create', [
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->validated('permissions') ?? []);

        return redirect()->route('settings.index', ['tab' => 'users'])->with('success', "Role \"{$role->name}\" created.");
    }

    public function edit(Role $role): View
    {
        return view('roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'rolePermissions' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $role->syncPermissions($request->validated('permissions') ?? []);

        return redirect()->route('settings.index', ['tab' => 'users'])->with('success', "Permissions for \"{$role->name}\" updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->with('error', "\"{$role->name}\" is a built-in role and can't be deleted.");
        }

        if ($role->users()->exists()) {
            return back()->with('error', "\"{$role->name}\" still has users assigned — reassign them first.");
        }

        $role->delete();

        return redirect()->route('settings.index', ['tab' => 'users'])->with('success', "Role \"{$role->name}\" deleted.");
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function permissionGroups(): array
    {
        $existing = Permission::query()->pluck('name')->all();

        return collect(self::PERMISSION_GROUPS)
            ->map(fn (array $names): array => array_values(array_intersect($names, $existing)))
            ->filter(fn (array $names): bool => $names !== [])
            ->all();
    }
}
