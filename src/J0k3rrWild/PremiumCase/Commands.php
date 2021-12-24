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
                        
                        $this->plugin->chanceArray[$i] = $this->plugin->cfg[$i]["szansa"];
                        // var_dump($this->plugin->chanceArray[$i]);
                    }
            
                    //    var_dump($this->plugin->chanceArray);
            
                    for ($i = 1; $i <= count($this->plugin->cfg); $i++) {
                        
                    $itemAmounts = $this->plugin->chanceArray[$i];
                    
                        
                        for ($y = 1; $y<=$itemAmounts; $y++) {
                        
                        $this->plugin->index++;
                        $this->plugin->idArray[$this->plugin->index] = $this->plugin->cfg[$i]["id"];
                        $this->plugin->nameArray[$this->plugin->index] = $this->plugin->cfg[$i]["nazwa"];
                        $this->plugin->amountArray[$this->plugin->index] = $this->plugin->cfg[$i]["ilosc"];
                            // var_dump($this->plugin->nameArray[$this->plugin->index]);
                            
                        }    
                    } 
                    $this->plugin->getLogger()->info(TF::GREEN."[PremiumCase] > Plugin oraz konfiguracje zostały przeładowane pomyślnie");
                    $p->sendMessage(TF::GREEN."[PremiumCase] > Plugin oraz konfiguracje zostały przeładowane pomyślnie");
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
                        $item->setCustomName(TF::GOLD."Pandorka");
                        $target->getInventory()->addItem($item);
                    
                
                    
    
                    $target->sendMessage(TF::GREEN."Otrzymałeś ".$args[2]." pandorek od ".$p->getName());
                    $p->sendMessage(TF::GREEN."Gracz ".$target->getName()." otrzymał ".$args[2]." pandorek");
                 }else{
                    $p->sendMessage(TF::RED."[PremiumCase] > Upewnij się że wpisałeś poprawny nick lub czy gracz jest online!");
                 }
             }else{
                 $p->sendMessage(TF::RED."[PremiumCase] > Upewnij się że wpisałeś wszystkie argumenty oraz ze są poprawne!");
                 $p->sendMessage(TF::RED."/pc give <nick> <ilosc>");
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
                        $item->setCount((int)$args[2]);
                        $item->setCustomName(TF::GOLD."Pandorka");
                        $amount = $this->amountArray[$chance];

                    for($i=1; $i<=$amount; $i++){
                        $target->getInventory()->addItem($item);
                    
                    }
                    $target->sendMessage(TF::GREEN."Otrzymałeś ".$args[1]." pandorek od ".$p->getName());

                  }else{
                    $count++;

                    $ident = new ItemIdentifier($this->plugin->pandorkaId, 0);
                    $item = new Item($ident);
                    $item->setCount((int)$args[2]);
                    $item->setCustomName(TF::GOLD."Pandorka");
                    $amount = $this->amountArray[$chance];

                    for($i=1; $i<=$amount; $i++){
                     $target->getInventory()->addItem($item);
                    
                    }
                    $target->sendMessage(TF::GREEN."[MeetMate] > Otrzymałeś ".$args[1]." pandorek od wlasciciela");
    
                  }
                 }
                }
                $p->sendMessage(TF::GREEN."[PremiumCase] > Przekazałeś po ".$args[1]." pandorek dla ".$count." graczy");
              }else{
                    $p->sendMessage(TF::RED."[PremiumCase] > Upewnij się że wpisales poprawny nick lub czy gracz jest online!");
                 } 
                }   
                break;
          }
         
          return true;
      }









}

