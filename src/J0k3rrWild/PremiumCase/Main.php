<?php



namespace J0k3rrWild\PremiumCase;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use pocketmine\item\Item;
use pocketmine\item\Block;
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
use J0k3rrWild\PremiumCase\Commands;
use pocketmine\item\ItemIdentifier;
use pocketmine\event\CancellableTrait;


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
  

    public function onEnable(): void{
        $server = $this->getServer();
        $server->getCommandMap()->register("premiumcase", new Commands($this));
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
      
        $this->index = 0;
        
        
        $this->cfg = $this->getConfig()->getAll();

            
        for ($i = 1; $i < count($this->cfg)+1; $i++) {
            
            $this->chanceArray[$i] = $this->cfg[$i]["chance"];
            
           }

      

        for ($i = 1; $i <= count($this->cfg); $i++) {
            
           $itemAmounts = $this->chanceArray[$i];
           
               
            for ($y = 1; $y<=$itemAmounts; $y++) {
            
             $this->index++;
             $this->idArray[$this->index] = $this->cfg[$i]["id"];
             $this->nameArray[$this->index] = $this->cfg[$i]["name"];
             $this->amountArray[$this->index] = $this->cfg[$i]["amount"];
               
                
            }    
        } 
    }
     
    //API 4.x = SHIT <-- FUNCTION TODO
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
        if($hand->getCustomName() === TF::GOLD."PremiumCase"){
       
         
         $chance = mt_rand(1, 100);

           
             
         $player->sendMessage("§8[§bPremiumCase§8] §fYou get §8(§b".$this->amountArray[$chance]."§8) §f".$this->nameArray[$chance]);
                       //Lotto identify 
                        $ident = new ItemIdentifier($this->idArray[$chance], 0);
                        $item = new Item($ident);
                        $item->setCount((int)$this->amountArray[$chance]);
                        
                      //Case identify
                      $identc = new ItemIdentifier($this->pandorkaId, 0);
                      $itemc = new Item($identc);
                      $itemc->setCount(1);

            $player->getInventory()->addItem($item); 
            $player->getInventory()->removeItem($itemc);
           
            // $this->effect($player);
            
           
           $pevent->cancel();
           
            }
        

  }
  
}




