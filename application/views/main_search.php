<div id="ankafly-main">    
    <div class="cleared reset-box">
    </div>    
    <div class="ankafly-box ankafly-sheet">        
        <div class="ankafly-box-body ankafly-sheet-body">            
            <div class="ankafly-header">
                <div class="cleared reset-box">
                </div>
                <div class="ankafly-logo">
                    <a href="#"><img src="<?php echo base_url("static/images/ankafly_logo.png"); ?>" width="300" /></a>                                                                        
                </div>                             
            </div>            
            <div class="cleared reset-box">
            </div>            
            <div class="ankafly-layout-wrapper">                
                <div class="ankafly-content-layout">                    
                    <div class="ankafly-content-layout-row">                        
                        <div class="ankafly-layout-cell ankafly-sidebar_anasayfa2">
                            <div class="ankafly-box ankafly-block">    
                                <div class="ankafly-box-body ankafly-block-body">                
                                    <div class="ankafly-bar ankafly-blockheader">                    
                                        <h3 class="t">UÇUŞ ARA</h3>                
                                    </div>                
                                    <div class="ankafly-box ankafly-blockcontent">                    
                                        <div class="ankafly-box-body ankafly-blockcontent-body">                


                                            <div id="UcusAraBlokAna2">
                                                <form id="UcusAraFormu" name="UcusAraFormu" method="post" action="">
                                                    <div class="UcusYon">
                                                        <label>
                                                            <input type="radio" name="UcusYon" value="tekyon" id="UcusYon1" />
                                                            Tek Yön</label>

                                                        <label>
                                                            <input type="radio" name="UcusYon" value="gidisdonus" id="UcusYon2" />
                                                            Gidiş-Dönüş</label>
                                                        <label>
                                                            <input type="radio" name="UcusYon" value="cokluucus" id="UcusYon3" />
                                                            Çoklu Uçuş</label>

                                                    </div>
                                                    <div style="float:left;">

                                                        <span>Kalkış : </span><input name="from" type="text" id="araiconana2" size="49"/>

                                                        <span>Varış &nbsp; : </span> <input name="to" type="text" id="araiconana2" size="49" />

                                                    </div>
                                                    <div>
                                                        <span>Gidiş &nbsp; : </span><input type="text" id="departure" name="departure" class="takvim" value="01/02/2013" size="13" maxlength="10" /> 
                                                        <span>Dönüş : </span><input type="text" id="to" name="return" class="takvim" value="08/02/2013" size="13" maxlength="10" />   
                                                    </div>      
                                                    <div id="YolcularAlani">
                                                        <div class="Yolcular">Yolcular</div>
                                                        <div style="float:left;">
                                                            <div id="YolcuTanim">
                                                                <h6 class="YolcularBaslik">Yetişkin <img src="images/info.png" title="Uçuş Değişiklik Notuna Girdiğiniz Değerin Yolculara Görünmesi İçin Seçili Hale Getiriniz" class="normalTip" height="16" width="16"/></h6>
                                                                <div align="center">
                                                                    <select class="YolcularBaslik" name="Yetiskin" size="1" id="yetiskin">
                                                                        <option>1</option>
                                                                        <option>2</option>
                                                                        <option>3</option>
                                                                        <option>4</option>
                                                                        <option>5</option>
                                                                        <option>6</option>
                                                                        <option>7</option>
                                                                        <option>8</option>
                                                                        <option>9</option>
                                                                    </select>
                                                                </div>
                                                                <h5 class="YolcularBaslik">12+ jaar</h5>
                                                            </div>

                                                        </div>
                                                        <div style="float:left;">
                                                            <div id="YolcuTanim">
                                                                <h6 class="YolcularBaslik">Çocuk <img src="images/info.png" height="16" width="16" title="Uçuş Değişiklik Notuna Girdiğiniz Değerin Yolculara Görünmesi İçin Seçili Hale Getiriniz" class="normalTip"/></h6>
                                                                <div align="center">
                                                                    <select class="YolcularBaslik" name="Cocuk" size="1" id="Cocuk">
                                                                        <option>0</option>
                                                                        <option>1</option>
                                                                        <option>2</option>
                                                                        <option>3</option>
                                                                        <option>4</option>
                                                                        <option>5</option>
                                                                        <option>6</option>
                                                                        <option>7</option>
                                                                        <option>8</option>
                                                                        <option>9</option>
                                                                    </select>
                                                                </div>
                                                                <h5 class="YolcularBaslik">2-11 jaar</h5>
                                                            </div>

                                                        </div>
                                                        <div style="float:left;">
                                                            <div id="YolcuTanim">
                                                                <h6 class="YolcularBaslik">Bebek <img src="images/info.png" height="16" width="16" title="Uçuş Değişiklik Notuna Girdiğiniz Değerin Yolculara Görünmesi İçin Seçili Hale Getiriniz" class="normalTip"/></h6>
                                                                <div align="center">
                                                                    <select class="YolcularBaslik" name="Bebek" size="1" id="Bebek">
                                                                        <option>0</option>
                                                                        <option>1</option>
                                                                        <option>2</option>
                                                                        <option>3</option>
                                                                        <option>4</option>
                                                                        <option>5</option>
                                                                        <option>6</option>
                                                                        <option>7</option>
                                                                        <option>8</option>
                                                                    </select>
                                                                </div>
                                                                <h5 class="YolcularBaslik">3-23 Maand</h5>
                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div align="left" style="line-height:40px; height:40px; float:left; width:100%">
                                                        <label>
                                                            <input name="UcusAramaSecenekleri" type="checkbox" id="UcusAramaSecenekleri_0" value="3gun" checked="checked" />+/- 3 Gün</label>
                                                        <label>
                                                            <input name="UcusAramaSecenekleri" type="checkbox" id="UcusAramaSecenekleri_1" value="direktucus" checked="checked" />Direkt Uçuşlar</label>
                                                        <label>
                                                            <input name="UcusAramaSecenekleri" type="checkbox" id="UcusAramaSecenekleri_2" value="lowcsot" checked="checked" />Low Cost</label>
                                                    </div><br />
                                                    <br />
                                                    <br />

                                                    <div style="float:left;">
                                                        <select name="sinif" size="1" id="sinif" style="width:120px">
                                                            <option>-- Sınıflar Hepsi --</option>
                                                            <option>Ekonomi</option>
                                                            <option>Business</option>
                                                            <option>First Class</option>
                                                        </select>
                                                        <select name="havasirketi" size="1" id="havasirketi">
                                                            <option>-- Hava Şirketleri --</option>
                                                            <option>Türk Hava Yolları</option>
                                                            <option>Corendon</option>
                                                            <option>Sky Airlines</option>
                                                            <option>Royal Air Maroc</option>
                                                            <option>Lufthansa</option>
                                                            <option>Air Arabia</option>
                                                        </select>
                                                    </div>
                                                </form>

                                            </div>  

                                            <div class="buttonwrapper" style="float:right">
                                                <a class="rezbuton" href="#" style="margin-right: 6px"><span>UÇUŞ ARA</span></a>
                                            </div>


                                            <div class="cleared">
                                            </div>                    
                                        </div>                
                                    </div>		
                                    <div class="cleared">
                                    </div>    
                                </div>

                            </div>   

                            <!--==========================================-->       


                            <!--==========================================-->     

                            <div class="cleared">
                            </div>                        
                        </div>

                        <div class="ankafly-layout-cell ankafly-content">                            
                            <!--==========================================-->                            
                            <div class="ankafly-box ankafly-post">    
                                <div class="ankafly-box-body ankafly-post-body">
                                    <div class="ankafly-post-inner ankafly-article">                                



                                        <div class="ankafly-postcontent">
                                            <!-- slider top-->
                                            <div id="header"><div class="wrap">
                                                    <div id="slide-holder">
                                                        <div id="slide-runner">
                                                            <a href=""><img id="slide-img-1" src="<?php echo base_url("static/images/slide_images/nature-photo.png"); ?>" class="slide" alt="" /></a>
                                                            <a href=""><img id="slide-img-2" src="<?php echo base_url("static/images/slide_images/nature-photo1.png"); ?>" class="slide" alt="" /></a>
                                                            <a href=""><img id="slide-img-3" src="<?php echo base_url("static/images/slide_images/nature-photo2.png"); ?>" class="slide" alt="" /></a>
                                                            <a href=""><img id="slide-img-4" src="<?php echo base_url("static/images/slide_images/nature-photo3.png"); ?>" class="slide" alt="" /></a>
                                                            <a href=""><img id="slide-img-5" src="<?php echo base_url("static/images/slide_images/nature-photo4.png"); ?>" class="slide" alt="" /></a>
                                                            <a href=""><img id="slide-img-6" src="<?php echo base_url("static/images/slide_images/nature-photo4.png"); ?>" class="slide" alt="" /></a>
                                                            <a href=""><img id="slide-img-7" src="<?php echo base_url("static/images/slide_images/nature-photo6.png"); ?>" class="slide" alt="" /></a> 
                                                            <div id="slide-controls">
                                                                <p id="slide-client" class="text"><strong>post: </strong><span></span></p>
                                                                <p id="slide-desc" class="text"></p>
                                                                <p id="slide-nav"></p>
                                                            </div>
                                                        </div>

                                                        <!--content featured gallery here -->
                                                    </div>
                                                    <script type="text/javascript">
                                                        if(!window.slider) var slider={};slider.data=[{"id":"slide-img-1","client":"nature beauty","desc":"photography"},{"id":"slide-img-2","client":"nature beauty","desc":"description here"},{"id":"slide-img-3","client":"nature beauty","desc":"add your"},{"id":"slide-img-4","client":"nature beauty","desc":"description here"},{"id":"slide-img-5","client":"nature beauty","desc":"description here"},{"id":"slide-img-6","client":"nature beauty","desc":"add your description here"},{"id":"slide-img-7","client":"nature beauty","desc":"add your description here"}];
                                                    </script>
                                                </div></div>
                                            <!--/header-->
                                        </div>                
                                        <div class="cleared">
                                        </div>


                                    </div>		
                                    <div class="cleared">
                                    </div>    
                                </div>     
                            </div> 
                            <!--===============================================-->  
                            <div class="ankafly-box ankafly-post">    
                                <div class="ankafly-box-body ankafly-post-body">
                                    <div class="ankafly-post-inner ankafly-article">                                
                                        <h2 class="ankafly-postheader">
                                            <span class="ankafly-postheadericon">Promosyonlar 

                                            </span></h2>                                                                

                                        <div class="ankafly-postcontent">
                                            <p>&nbsp;</p>                
                                        </div>                
                                        <div class="cleared">
                                        </div>                                
                                        <div class="ankafly-postmetadatafooter">                                        
                                            <div class="ankafly-postfootericons ankafly-metadata-icons">                        
                                                <span class="ankafly-postcategoryicon">Category: 
                                                    <span class="ankafly-post-metadata-category-name">
                                                        <a href="#">News</a>
                                                    </span>
                                                </span>                    
                                            </div>                                    
                                        </div>                
                                    </div>		
                                    <div class="cleared">
                                    </div>    
                                </div>     
                            </div>
                            <div class="ankafly-box ankafly-post">    

                            </div>                          
                            <div class="cleared">
                            </div>                        
                        </div>                    
                    </div>

                </div> 

            </div>            
            <div class="cleared">
            </div>            
            <div class="ankafly-footer">                
                <div class="ankafly-footer-body">                    
                    <a href="#" class="ankafly-rss-tag-icon" title="RSS"></a>                            
                    <div class="ankafly-footer-text">                                
                        <p>
                            <a href="#">Link1</a> | 
                            <a href="#">Link2</a> | 
                            <a href="#">Link3</a>
                        </p>
                        <p>Copyright © 2013. All Rights Reserved.
                        </p>                                                            
                    </div>                    
                    <div class="cleared">
                    </div>                
                </div>            
            </div>    		
            <div class="cleared">
            </div>        
        </div>    
    </div>    
    <div class="cleared">
    </div>    
    <p class="ankafly-page-footer">
    </p>    
    <div class="cleared">
    </div>
</div>