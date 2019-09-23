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
        Template::group('admin_managers::timestamps'),
        Template::group('admin_managers::details'),
    ],
    'fields' => [
        // main
        Template::string('admin_managers::id', 'general/common', '', [], 'fields.id_info', '', 'admin_managers::view|none'),
        Template::string('admin_managers::email', 'general/common', '', [], '', 'required|email|unique:managers,email,%id', 'admin_managers::view|admin_managers::edit_login'),
        Template::string('admin_managers::password', 'general/common', '', [], 'admin_managers::template.info_password', 'nullable|min:6', 'admin_managers::view|admin_managers::edit_login'),
        Template::checkbox('admin_managers::blocked', 'general/common', false, 'admin_managers::template.info_blocked', '', 'admin_managers::view|admin_managers::disable'),
        // details
        Template::phone('admin_managers::details.phone', 'general/details', '', '', '\+\7 (111) 111-11-11', '', 'admin_managers::view|admin_managers::edit'),
        Template::string('admin_managers::details.nickname', 'general/details', '', [], '', 'required', 'admin_managers::view|admin_managers::edit'),
        Template::string('admin_managers::details.last_name', 'general/details', '', [], '', '', 'admin_managers::view|admin_managers::edit'),
        Template::string('admin_managers::details.first_name', 'general/details', '', [], '', '', 'admin_managers::view|admin_managers::edit'),
        Template::string('admin_managers::details.middle_name', 'general/details', '', [], '', '', 'admin_managers::view|admin_managers::edit'),
        // timestamps
        Template::datetime('admin_managers::last_password_change', 'general/timestamps', null, 'admin_managers::template.info_last_password_change', '', 'admin_managers::view|none'),
        Template::datetime('admin_managers::last_login', 'general/timestamps', null, 'admin_managers::template.info_last_login', '', 'admin_managers::view|none'),
        Template::datetime('admin_managers::created_at', 'general/timestamps', null, 'admin_managers::template.info_created_at', '', 'admin_managers::view|none'),
        Template::datetime('admin_managers::deleted_at', 'general/timestamps', null, 'admin_managers::template.info_deleted_at', '', 'admin_managers::view|none'),
        // policies
        Template::checkboxList('admin_managers::policies', 'policies/', [], Template::makeCheckList(PermissionGroup::class), '', '', 'admin_permission_groups::edit|admin_permission_groups::edit'),
    ],
];
