<?php

namespace Modules\Opx\Users\Templates;

use Core\Foundation\Template\Template;
use Modules\Admin\PermissionGroups\Models\PermissionGroup;

/**
 * HELP:
 *
 * ID parameter is shorthand for defining module and field name separated by `::`.
 * [$module, $name] = explode('::', $id, 2);
 * $captionKey = "{$module}::template.section_{$name}";
 *
 * PLACEMENT is shorthand for section and group of field separated by `/`.
 * [$section, $group] = explode('/', $placement);
 *
 * PERMISSIONS is shorthand for read permission and write permission separated by `|`.
 * [$readPermission, $writePermission] = explode('|', $permissions, 2);
 */

return [
    'sections' => [
        Template::section('admin_managers::general'),
        Template::section('admin_managers::policies'),
    ],
    'groups' => [
        Template::group('admin_managers::common'),
        Template::group('admin_managers::details'),
    ],
    'fields' => [
        // main
        Template::string('admin_managers::email', 'general/common', '', [], '', 'required|email|unique:managers'),
        Template::string('admin_managers::password', 'general/common', '', [], 'admin_managers::template.info_password', 'nullable|min:6'),
        Template::checkbox('admin_managers::blocked', 'general/common', false, 'admin_managers::template.info_blocked'),
        // details
        Template::phone('admin_managers::details_phone', 'general/details', '', '', '\+\7 (111) 111-11-11'),
        Template::string('admin_managers::details_nickname', 'general/details', '', [], '', 'required'),
        Template::string('admin_managers::details_last_name', 'general/details'),
        Template::string('admin_managers::details_first_name', 'general/details'),
        Template::string('admin_managers::details_middle_name', 'general/details'),
        // policies
        Template::checkboxList('admin_managers::policies', 'policies/', [], Template::makeCheckList(PermissionGroup::class), '', '', 'admin_permission_groups::edit|admin_permission_groups::edit'),
    ],
];
