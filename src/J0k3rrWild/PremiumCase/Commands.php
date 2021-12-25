<?php

declare(strict_types = 1);


namespace J0k3rrWild\PremiumCase;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\Command;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandExecutor;
use pocketmine\plugin\{PluginOwned, PluginOwnedTrait};
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\ItemFactory;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

use J0k3rrWild\PremiumCase\Main;
 

class Commands extends Command implements PluginOwned{
    use PluginOwnedTrait;
    
    public function __construct(Main $plugin){
		parent::__construct("premiumcase", "Główna komenda pandorek", "/pandorka giveall/give <amount> | reload", ["pandorka", "pd", "pc", "premiumcase"]);
		$this->setPermission("premiumcase.give");
		$this->plugin = $plugin;
	}

    public function execute(CommandSender $p, string $label, array $args){
        if(!isset($args[0])){ 
            throw new InvalidCommandSyntaxException;
            return false;
       }
        
        $action = strtolower($args[0]);
        
        
        switch($action){ 
            case "reload":
                if($p->hasPermission("premiumcase") || $p->hasPermission("premiumcase.reload")){
                    $this->plugin->index = 0;
                    $this->plugin->reloadConfig();
                    $this->plugin->cfg = $this->plugin->getConfig()->getAll();
            
                        
                    for ($i = 1; $i < count($this->plugin->cfg)+1; $i++) {
                        
                        $this->plugin->chanceArray[$i] = $this->plugin->cfg[$i]["chance"];
                    
                    }
            
                    
                    for ($i = 1; $i <= count($this->plugin->cfg); $i++) {
                        
                    $itemAmounts = $this->plugin->chanceArray[$i];
                    
                        
                        for ($y = 1; $y<=$itemAmounts; $y++) {
                        
                        $this->plugin->index++;
                        $this->plugin->idArray[$this->plugin->index] = $this->plugin->cfg[$i]["id"];
                        $this->plugin->nameArray[$this->plugin->index] = $this->plugin->cfg[$i]["name"];
                        $this->plugin->amountArray[$this->plugin->index] = $this->plugin->cfg[$i]["amount"];
                            
                            
                        }    
                    } 
                    $this->getLogger()->info(TF::GREEN."[PremiumCase] > Plugin and configuration has been reloaded");
                    $p->sendMessage(TF::GREEN."[PremiumCase] > Plugin and configuration has been reloaded");
                    return true;
                }
                break; 
            case "give":
             if($p->hasPermission("premiumcase") || $p->hasPermission("premiumcase.give")){
               
               
                if(isset($args[2]) && is_numeric($args[2]) ){
                 
                 
                 if($target = $this->plugin->getServer()->getPlayerExact($args[1])){
        
                        $ident = new ItemIdentifier($this->plugin->pandorkaId, 0);
                        $item = new Item($ident);
                        $item->setCount((int)$args[2]);
                        $item->setCustomName(TF::GOLD."PremiumCase");
                        $target->getInventory()->addItem($item);
                    
                
                    
    
                        $target->sendMessage(TF::GREEN."[PremiumCase] > You got ".$args[2]." premium cases from ".$p->getName());
                        $p->sendMessage(TF::GREEN."Player ".$target->getName()." get ".$args[2]." premium cases");
                 }else{
                    $p->sendMessage(TF::RED."[PremiumCase] > Make sure you enter the correct nickname or that the player is online!");
                 }
             }else{
                $p->sendMessage(TF::RED."[PremiumCase] > Make sure you enter all arguments and that they are correct!");
                 return false;
             } 
           
             
            }
            break;
            case "giveall":
             if($p->hasPermission("premiumcase") || $p->hasPermission("premiumcase.give")){  
              if(isset($args[1]) && is_numeric($args[1])){
                $count = 0;
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $target) {
                 if($p!==$target){
                  if($p->getName() != "CONSOLE"){
                    $count++;
    
                        $ident = new ItemIdentifier($this->plugin->pandorkaId, 0);
                        $item = new Item($ident);
                        $item->setCount((int)$args[1]);
                        $item->setCustomName(TF::GOLD."PremiumCase");
                        

                    
                        $target->getInventory()->addItem($item);
                    
                    
                    $target->sendMessage(TF::GREEN."[PremiumCase] > You got ".$args[2]." premium cases from ".$p->getName());

                  }else{
                    $count++;

                    $ident = new ItemIdentifier($this->plugin->pandorkaId, 0);
                    $item = new Item($ident);
                    $item->setCount((int)$args[1]);
                    $item->setCustomName(TF::GOLD."PremiumCase");
                    $target->getInventory()->addItem($item);

                    $target->sendMessage(TF::GREEN."[PremiumCase] > You got ".$args[1]." premium cases from owner");
    
                  }
                 }
                }
                $p->sendMessage(TF::GREEN."[PremiumCase] > You gave ".$args[1]." premium cases to ".$count." players");
          }else{
                $p->sendMessage(TF::RED."[PremiumCase] > Make sure you enter the correct nickname or that the player is online!");

                 } 
                }   
                break;
          }
         
          return true;
      }









}

