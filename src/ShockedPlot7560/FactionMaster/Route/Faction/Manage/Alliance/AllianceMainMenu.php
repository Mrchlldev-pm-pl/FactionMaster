<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /  
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/ 
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560 
 * @link https://github.com/ShockedPlot7560
 * 
 *
*/

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\ManageFactionMain;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class AllianceMainMenu implements Route {

    const SLUG = "allianceMain";

    public $PermissionNeed = [
        Ids::PERMISSION_SEND_ALLIANCE_INVITATION,
        Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION,
        Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND,
        Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND
    ];
    public $backMenu;

    /** @var array */
    private $buttons;
    /** @var FactionEntity */
    private $FactionEntity;
    /** @var UserEntity */
    private $UserEntity;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(ManageFactionMain::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->FactionEntity = MainAPI::getFactionOfPlayer($player->getName());
        $this->UserEntity = $User;
        $this->permissions = $UserPermissions;

        $message = '';
        if (isset($params[0])) $message = $params[0] . "\n";
        $this->buttons = [];
        foreach ($this->FactionEntity->ally as $Alliance) {
            $this->buttons[] = MainAPI::getFaction($Alliance)->name;
        }
        if (count($this->FactionEntity->ally) == 0) {
           $message .= Utils::getText($this->UserEntity->name, "NO_ALLY");
        }
        if ((isset($UserPermissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) && $UserPermissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) || 
            $this->UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_SEND_INVITATION");
        if ((isset($UserPermissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $UserPermissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
            $this->UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_INVITATION_PENDING");
        if ((isset($UserPermissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) && $UserPermissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) || 
            (isset($permisUserPermissionssions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) && $UserPermissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) || 
            $this->UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_REQUEST_PENDING");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");
        $menu = $this->allianceMainMenu($message);
        $player->sendForm($menu);;
    }

    public function call() : callable{
        $permissions = $this->permissions;
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($permissions, $backMenu){
            if ($data === null) return;
            if ($data == (\count($this->buttons) - 1)) {
                Utils::processMenu($backMenu, $Player);
                return;
            }
            $allyNumber = count($this->FactionEntity->ally);
            switch ($data) {
                case $allyNumber:
                    if ((isset($permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            Utils::processMenu(RouterFactory::get(NewAllianceInvitation::SLUG), $Player);
                    }else
                    if ((isset($permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            Utils::processMenu(RouterFactory::get(AllianceInvitationList::SLUG), $Player);
                    }else
                    if ((isset($permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) || 
                        (isset($permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player);
                    }
                    break;
                case $allyNumber + 1:
                    if ((isset($permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            if ((isset($permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
                                $this->UserEntity->rank == Ids::OWNER_ID) {
                                    Utils::processMenu(RouterFactory::get(AllianceInvitationList::SLUG), $Player);
                            }else
                            if ((isset($permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) || 
                                (isset($permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) || 
                                $this->UserEntity->rank == Ids::OWNER_ID) {
                                    Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player);
                            }
                    }else
                    if ((isset($permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            Utils::processMenu(RouterFactory::get(AllianceInvitationList::SLUG), $Player);
                    }
                    break;
                case $allyNumber + 2:
                    Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player);
                    break;
                default:
                    Utils::processMenu(RouterFactory::get(ManageAlliance::SLUG), $Player, [MainAPI::getFaction($this->FactionEntity->ally[$data])]);
                    break;
            }
        };
    }

    private function allianceMainMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_ALLIANCE_MAIN_TITLE"));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}