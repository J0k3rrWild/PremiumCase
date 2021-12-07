<?php



namespace J0k3rrWild\PremiumCase;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\block\BlockPlaceEvent; 
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Server;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\network\mcpe\protocol\{AddEntityPacket, ExplodePacket, RemoveEntityPacket, UseItemPacket};
use Refaltor\Natof\CustomItem\CustomItem;


class Main extends PluginBase implements Listener{

  //Nazwa głównego configa
  const SETTING_FILE = "config.yml";
  public $cfg;
  public $arrays;
  public $chanceArray;
  public $itemsArray;
  public $itemArray;
  public $idArray;
  public $nameArray;
  public $amountArray;
  public $index;
  public $item;
  public $pandorkaId = 130;
  

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        $this->getLogger()->info(TF::GREEN."[PremiumCase] > Plugin oraz konfiguracja została załadowana pomyślnie");
        $this->index = 0;
        
        // Wczytanie configa
        $this->cfg = $this->getConfig()->getAll();

            
        for ($i = 1; $i < count($this->cfg)+1; $i++) {
            
            $this->chanceArray[$i] = $this->cfg[$i]["szansa"];
            // var_dump($this->chanceArray[$i]);
           }

        //    var_dump($this->chanceArray);

        for ($i = 1; $i <= count($this->cfg); $i++) {
            
           $itemAmounts = $this->chanceArray[$i];
           
               
            for ($y = 1; $y<=$itemAmounts; $y++) {
            
             $this->index++;
             $this->idArray[$this->index] = $this->cfg[$i]["id"];
             $this->nameArray[$this->index] = $this->cfg[$i]["nazwa"];
             $this->amountArray[$this->index] = $this->cfg[$i]["ilosc"];
                // var_dump($this->nameArray[$this->index]);
                
            }    
        } 
    }

    public function effect($player){

        $light = new AddActorPacket();
        $light->type = "minecraft:lightning_bolt";
        $light->entityRuntimeId = Entity::$entityCount++;
        $light->metadata = [];
        $light->motion = null;
        $light->yaw = $player->getYaw();
        $light->pitch = $player->getPitch();
        $light->position = new Vector3($player->getX(), $player->getY(), $player->getZ());
        Server::getInstance()->broadcastPacket($player->getLevel()->getPlayers(), $light);
        $block = $player->getLevel()->getBlock($player->getPosition()->floor()->down());
        $particle = new DestroyBlockParticle(new Vector3($player->getX(), $player->getY(), $player->getZ()), $block);
        $player->getLevel()->addParticle($particle);
        $sound = new PlaySoundPacket();
        $sound->soundName = "ambient.weather.thunder";
        $sound->x = $player->getX();
        $sound->y = $player->getY();
        $sound->z = $player->getZ();
        $sound->volume = 1;
        $sound->pitch = 1;
        Server::getInstance()->broadcastPacket($player->getLevel()->getPlayers(), $sound);
}


    
    public function onPlace(BlockPlaceEvent $pevent){
        $player = $pevent->getPlayer();
       
        $hand = $player->getInventory()->getItemInHand();
        if($hand->getId() === $this->pandorkaId){
        //  $block = $this->getBlock();
         
         $chance = mt_rand(1, 100);

           
             
            $player->sendMessage("§8[§bPandorka§8] §fZnaleziono §8(§b".$this->amountArray[$chance]."§8) §f".$this->nameArray[$chance]);
            
            $player->getInventory()->addItem(Item::get($this->idArray[$chance], 0, $this->amountArray[$chance])); 
            $player->getInventory()->removeItem(Item::get($this->pandorkaId, 0, 1));
           
            $this->effect($player);
            
            $pevent->setCancelled(true);
           
            }
        

  }
  
 
  public function onCommand(CommandSender $p, Command $cmd, string $label, array $args) : bool{
    if(!isset($args[0])) return false;
    
    $action = strtolower($args[0]);
    
    
    switch($action){ 
        case "reload":
            if($p->hasPermission("premiumcase") || $p->hasPermission("premiumcase.reload")){
                $this->index = 0;
                $this->reloadConfig();
                $this->cfg = $this->getConfig()->getAll();
        
                    
                for ($i = 1; $i < count($this->cfg)+1; $i++) {
                    
                    $this->chanceArray[$i] = $this->cfg[$i]["szansa"];
                    // var_dump($this->chanceArray[$i]);
                }
        
                //    var_dump($this->chanceArray);
        
                for ($i = 1; $i <= count($this->cfg); $i++) {
                    
                $itemAmounts = $this->chanceArray[$i];
                
                    
                    for ($y = 1; $y<=$itemAmounts; $y++) {
                    
                    $this->index++;
                    $this->idArray[$this->index] = $this->cfg[$i]["id"];
                    $this->nameArray[$this->index] = $this->cfg[$i]["nazwa"];
                    $this->amountArray[$this->index] = $this->cfg[$i]["ilosc"];
                        // var_dump($this->nameArray[$this->index]);
                        
                    }    
                } 
                $this->getLogger()->info(TF::GREEN."[PremiumCase] > Plugin oraz konfiguracje zostały przeładowane pomyślnie");
                $p->sendMessage(TF::GREEN."[PremiumCase] > Plugin oraz konfiguracje zostały przeładowane pomyślnie");
                return true;
            }
            break; 
        case "give":
         if($p->hasPermission("premiumcase") || $p->hasPermission("premiumcase.give")){
           
           
            if(isset($args[2]) && is_numeric($args[2]) ){
             
             
             if($checker = $this->getServer()->getPlayer($args[1])){
                $target = $this->getServer()->getPlayer($args[1]);
                $target->getInventory()->addItem(Item::get($this->pandorkaId, 0, $args[2]));
                // $this->onItemHeld();
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
            foreach ($this->getServer()->getOnlinePlayers() as $target) {
             if($p!==$target){
              if($p->getName() != "CONSOLE"){
                $count++;
                $target->getInventory()->addItem(Item::get($this->pandorkaId, 0, $args[1]));
                $target->sendMessage(TF::GREEN."Otrzymałeś ".$args[1]." pandorek od ".$p->getName());
              }else{
                $count++;
                $target->getInventory()->addItem(Item::get($this->pandorkaId, 0, $args[1]));
                $target->sendMessage(TF::GREEN."Otrzymałeś ".$args[1]." pandorek od wlasciciela");

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




