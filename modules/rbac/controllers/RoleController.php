<?php

namespace app\modules\rbac\controllers;

use yii\rbac\Item;
use app\modules\rbac\base\ItemController;

/**
 * Class RoleController
 *
 * @package app\modules\rbac\controllers
 */
class RoleController extends ItemController
{
    /**
     * @var int
     */
    protected $type = Item::TYPE_ROLE;

    /**
     * @var array
     */
    protected $labels = [
        'Item' => 'Role',
        'Items' => 'Roles',
    ];
}
