<?php

use App\Folders\Audiences\DepartmentAudience;
use App\Models\Folder;
use App\Models\User;

$aud = new DepartmentAudience();

// 1. Value parsing
var_dump($aud::parseValue('12') === ['12', null]);
var_dump($aud::parseValue('12:primary') === ['12', 'primary']);
var_dump($aud::scopedValues('5') === ['5', '5:primary', '5:secondary']);

// 2. A real user with a primary department
$u = User::whereNotNull('department_id')->where('role', 'admin')->first();
if ($u) {
    echo "User {$u->id} primary dept: {$u->department_id}; secondaries: " . $u->secondaryDepartments->pluck('id')->join(',') . PHP_EOL;
    print_r($aud->userValues($u));

    $map = Folder::audienceMembershipMap($u);
    echo 'membership map department values: ' . implode(', ', $map['department'] ?? []) . PHP_EOL;

    $d = (string) $u->department_id;
    // Bare id (legacy "all members") and primary-scoped must match; a foreign dept must not.
    $mk = fn ($v) => (object) ['audience_type' => 'department', 'audience_value' => $v, 'permission' => 'viewer'];
    echo 'legacy bare grant matches: ' . var_export(Folder::matchGrantsPermission([$mk($d)], $map) === 'viewer', true) . PHP_EOL;
    echo 'primary-scoped grant matches: ' . var_export(Folder::matchGrantsPermission([$mk($d . ':primary')], $map) === 'viewer', true) . PHP_EOL;
    echo 'secondary-scoped grant matches (should be false unless also secondary): '
        . var_export(Folder::matchGrantsPermission([$mk($d . ':secondary')], $map), true) . PHP_EOL;
    echo 'foreign dept grant matches (should be false): '
        . var_export(Folder::matchGrantsPermission([$mk('999999')], $map), true) . PHP_EOL;

    // 3. membersQuery counts per scope
    echo "members all={$aud->membersQuery($d)->count()} primary={$aud->membersQuery($d . ':primary')->count()} secondary={$aud->membersQuery($d . ':secondary')->count()}" . PHP_EOL;

    // 4. valueLabel
    echo $aud->valueLabel($d) . ' | ' . $aud->valueLabel($d . ':primary') . ' | ' . $aud->valueLabel($d . ':secondary') . PHP_EOL;
} else {
    echo 'No role=admin user with a primary department found.' . PHP_EOL;
}
