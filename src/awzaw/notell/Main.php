<?php

namespace awzaw\notell;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {

    private $opSend;
    private $opRecv;
    private $playerSend;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->reloadConfig();

        $this->opSend = false;
        $this->opRecv = false;
        $this->playerSend = [];

        $cfg = $this->getConfig()->getAll();
        foreach($cfg as $key=>$value) {
            switch(strtolower($key)) {
                case 'player-send':
                    if(is_array($value)) {
                        foreach($value as $name=>$enabled) {
                            if($enabled === true) {
                                $this->playerSend[$name] = true;
                            }
                        }
                    }
                    break;
                case 'op-send':
                    if($value === true) {
                        $this->opSend = true;
                    }
                    break;
                case 'op-recv':
                    if($value === true) {
                        $this->opRecv = true;
                    }
                    break;
            }
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args) : bool{
        if (strtolower($cmd->getName()) !== "notell")
            return false;

        $player = $issuer->getName();
        $isOp = $issuer->isOp();

        /*
            /notell
            /notell <player>
            /notell <player> <on|off>
            /notell opsend
            /notell opsend <on|off>
            /notell oprecv
            /notell oprecv <on|off>
        */

        $dirty = false;
        if(!(isset($args[0]))) {
            // notell
            $msg = '';
            if($this->opRecv) {
                $msg .= 'Players can send to OP. ';
            } else {
                $msg .= 'Players cannot send to OP. ';
            }
            
            if($this->opSend) {
                $msg .= 'OP can send to Players. ';
            } else {
                $msg .= 'OP cannot send to Players. ';
            }
            
            if(isset($this->playerSend[$player])) {
                $msg .= 'You can send to Players';
            } else {
                $msg .= 'You cannot send to Players';
            }

            $issuer->sendMessage(TEXTFORMAT::GREEN . $msg);
        } else {
            if($args[0] === 'opsend') {
                if(!(isset($args[1]))) {
                    // notell opsend
                    $issuer->sendMessage(TEXTFORMAT::GREEN . "opsend is set to ".($this->opSend?'on':'off'));
                } else {
                    if($args[1] === 'on') {
                        // notell opsend on
                        if($this->opSend != true) {
                            $dirty = true;
                            $this->getConfig()->set('op-send', true);
                            $this->opSend = true;
                        }
                        
                        $issuer->sendMessage(TEXTFORMAT::GREEN . "opsend is set to on");
                    } elseif($args[1] === 'off') {
                        // notell opsend off
                        if($this->opSend == true) {
                            $dirty = true;
                            $this->getConfig()->set('op-send', false);
                            $this->opSend = false;
                        }
                        
                        $issuer->sendMessage(TEXTFORMAT::GREEN . "opsend is set to off");
                    } else {
                        // Bad
                        $issuer->sendMessage(TEXTFORMAT::RED . "opsend only accepts on|off");
                    }
                }
            } elseif($args[0] == 'oprecv') {
                if(!(isset($args[1]))) {
                    // notell oprecv
                    $issuer->sendMessage(TEXTFORMAT::GREEN . "oprecv is set to ".($this->opRecv?'on':'off'));
                } else {
                    if($args[1] === 'on') {
                        // notell oprecv on
                        if($this->opRecv != true) {
                            $dirty = true;
                            $this->getConfig()->set('op-recv', true);
                            $this->opRecv = true;
                        }

                        $issuer->sendMessage(TEXTFORMAT::GREEN . "oprecv is set to on");
                    } elseif($args[1] === 'off') {
                        // notell oprecv off
                        if($this->opRecv == true) {
                            $dirty = true;
                            $this->getConfig()->set('op-recv', false);
                            $this->opRecv = false;
                        }
                        
                        $issuer->sendMessage(TEXTFORMAT::GREEN . "oprecv is set to off");
                    } else {
                        // Bad
                        $issuer->sendMessage(TEXTFORMAT::RED . "oprecv only accepts on|off");
                    }
                }
            } else {
                if(!(isset($args[1]))) {
                    // notell <player>
                    if(!isset($this->playerSend[$args[0]])) {
                        $issuer->sendMessage(TEXTFORMAT::GREEN . $args[0]." cannot send messages");
                    } else {
                        $issuer->sendMessage(TEXTFORMAT::GREEN . $args[0]." can send messages");
                    }
                } else {
                    if($args[1] === 'on') {
                        // notell <player> on
                        if(!(isset($this->playerSend[$args[0]]))) {
                            $dirty = true;
                            $this->getConfig()->setNested('player-send.'.$args[0], true);
                            $this->playerSend[$args[0]] = true;
                        }

                        $issuer->sendMessage(TEXTFORMAT::GREEN . $args[0]." can now send messages");
                    } elseif($args[1] === 'off') {
                        // notell <player> off
                        if(isset($this->playerSend[$args[0]])) {
                            $dirty = true;
                            $this->getConfig()->removeNested('player-send.'.$args[0]);
                            unset($this->playerSend[$args[0]]);
                        }

                        $issuer->sendMessage(TEXTFORMAT::GREEN . $args[0]." can no longer send messages");
                    } else {
                        // Bad
                        $issuer->sendMessage(TEXTFORMAT::RED . "<player> only accepts on|off");
                    }
                }
            }
        }

        if($dirty) {
            $this->getConfig()->save();
        }
        
        return true;
    }

    public function onPlayerCommand(PlayerCommandPreprocessEvent $event) {
        if ($event->isCancelled()) return;

        $message = $event->getMessage();

        if (strtolower(substr($message, 0, 5) === "/tell") || strtolower(substr($message, 0, 4) === "/msg")) {
            $command = substr($message, 1);
            $args = explode(" ", $command);
            if (!isset($args[1])) {
                return true;
            }

            if($event->getPlayer()->isOp()) {
                if($this->opSend) {
                    return;
                }
            }

            $sender = $event->getPlayer();

            $receiver = $this->getServer()->getPlayer($args[1]);
            if($receiver == null) {
                $sender->sendMessage(TextFormat::RED . "Could not find player ".$args[1]);
                $event->setCancelled(true);
                return;
            }

            if($receiver->isOp() && $this->opRecv) {
                return;
            }

            if(isset($this->playerSend[$sender->getName()])) {
                return;
            }

            $sender->sendMessage(TextFormat::RED . "You are not allowed to send private messages to ".$args[1]);
            $event->setCancelled(true);
        }
    }

    public function onQuit(PlayerQuitEvent $e) {
        if (isset($this->playerSend[$e->getPlayer()->getName()])) {
            unset($this->enabled[$e->getPlayer()->getName()]);
        }
    }
}
