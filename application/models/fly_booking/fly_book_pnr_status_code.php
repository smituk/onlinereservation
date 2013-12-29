<?php  
  class PnrStatusCode{
      const SUCCESS = "S"; // Bu Pnr başarılı bir şekilde biletlenebilir
      const AIRSEGMENT_SELL_FAILURE = "A"; // Pnr alınmıs fakat air segmentin bir tanesinde sıkıntı çıkmışşsa
      const PRICE_CHANGED ="P";  //fiyat değişmiş
      const SCHEDULE_CHANGED = "SC"; // schedude Değişmiş;
      const BOTHCHANGED = "BT"; // schedule ve fiyat değişmis;
      const AIRSEGMENTNOTHK = "NHK";
      
  }
?>


