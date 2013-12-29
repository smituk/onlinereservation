<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<style>
    label.radio{
        font-size: 13px;
        color: #090000;
        font-weight: bold;

    }

    body{
        font-size: 11px;
        background-color: #ffffff;background-image:url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxMDAlJyBoZWlnaHQ9JzEwMCUnPgoJPGRlZnM+CgkJPHBhdHRlcm4gcGF0dGVyblVuaXRzPSd1c2VyU3BhY2VPblVzZScgaWQ9J2MnIHdpZHRoPScxMScgaGVpZ2h0PScyMicgeD0nMCcgeT0nMCcgdmlld0JveD0nMCAwIDUgMTAnPgoJCQk8bGluZSB4MT0nLTInIHkxPScxJyB4Mj0nNycgeTI9JzEwJyBzdHJva2U9JyNmMGYwZjAnIHN0cm9rZS13aWR0aD0nLjUnLz4KCQkJPGxpbmUgeDE9Jy0yJyB5MT0nNicgeDI9JzcnIHkyPScxNScgc3Ryb2tlPScjZjBmMGYwJyBzdHJva2Utd2lkdGg9Jy41Jy8+CgkJCTxsaW5lIHgxPSctMicgeTE9Jy00JyB4Mj0nNycgeTI9JzUnIHN0cm9rZT0nI2YwZjBmMCcgc3Ryb2tlLXdpZHRoPScuNScvPgoJCTwvcGF0dGVybj4KCQk8cmFkaWFsR3JhZGllbnQgaWQ9J2cnIGdyYWRpZW50VW5pdHM9J3VzZXJTcGFjZU9uVXNlJyBjeD0nNTAlJyBjeT0nNTAlJyByPSc3NSUnIGZ4PSc0NiUnIGZ5PScyMiUnPgoJCQk8c3RvcCBvZmZzZXQ9JzAlJyBzdG9wLW9wYWNpdHk9Jy4yJyBzdG9wLWNvbG9yPScjZmZmZmZmJyAvPgoJCQk8c3RvcCBvZmZzZXQ9JzEwMCUnIHN0b3AtY29sb3I9JyNiM2NhZTAnIC8+CgkJPC9yYWRpYWxHcmFkaWVudD4KCTwvZGVmcz4KCTxyZWN0IHdpZHRoPScxMDAlJyBoZWlnaHQ9JzEwMCUnIGZpbGw9J3VybCgjYyknLz4KCTxyZWN0IHdpZHRoPScxMjAlJyB4PSctMTAlJyB5PSctMTAlJyBoZWlnaHQ9JzEyMCUnIGZpbGw9J3VybCgjZyknLz4KPC9zdmc+');
        height: 100%;
        width: 100%;
    }

    .fly-direction-option{
        padding: 15px 35px;

        border-bottom: 1px solid #91bac4;
        margin-left: 15px;

    }
    .fly-search-form{
        background: rgb(179,220,237); /* Old browsers */
        background: -moz-linear-gradient(top,  rgba(179,220,237,1) 0%, rgba(41,184,229,1) 48%, rgba(188,224,238,1) 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(179,220,237,1)), color-stop(48%,rgba(41,184,229,1)), color-stop(100%,rgba(188,224,238,1))); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top,  rgba(179,220,237,1) 0%,rgba(41,184,229,1) 48%,rgba(188,224,238,1) 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top,  rgba(179,220,237,1) 0%,rgba(41,184,229,1) 48%,rgba(188,224,238,1) 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top,  rgba(179,220,237,1) 0%,rgba(41,184,229,1) 48%,rgba(188,224,238,1) 100%); /* IE10+ */
        background: linear-gradient(to bottom,  rgba(179,220,237,1) 0%,rgba(41,184,229,1) 48%,rgba(188,224,238,1) 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#b3dced', endColorstr='#bce0ee',GradientType=0 ); /* IE6-9 */
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        border-top: 1px solid #eaf3f7;
        padding: 10px;
        min-height: 300px;
    }


    .fly-airpot-container{
        min-height: 50px;
        padding: 10px 0px;

    }
    .fly-airpot-container div{

        margin-bottom: 5px;
    }

    .fly-customer-detail{
        padding: 10px;
    }

    .fly-customer-detail .selectboxit-container .selectboxit{
        width: 50px;
        margin: 2px 3px;
    }

    .selectboxit-arrow-container {

        /* Encloses the down arrow in a box */
        border-left: 1px solid #ccc;
        width: 30px;

    }
    img.ui-datepicker-trigger{
        margin-left: 2px;
    }
    .select2-container{
        width: 100%;
    }
    #domMessage{
        background-color: transparent !important;
        height: 80px;

    }
    #domMessage h1{
        font-size: 13px;
        background: rgb(254,255,255); /* Old browsers */
        background: -moz-linear-gradient(top, rgba(254,255,255,1) 0%, rgba(210,235,249,1) 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(254,255,255,1)), color-stop(100%,rgba(210,235,249,1))); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top, rgba(254,255,255,1) 0%,rgba(210,235,249,1) 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top, rgba(254,255,255,1) 0%,rgba(210,235,249,1) 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top, rgba(254,255,255,1) 0%,rgba(210,235,249,1) 100%); /* IE10+ */
        background: linear-gradient(to bottom, rgba(254,255,255,1) 0%,rgba(210,235,249,1) 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#feffff', endColorstr='#d2ebf9',GradientType=0 ); /* IE6-9 */
        margin: 0;

    }

    #s2id_flight-type-select{
        width: 100px;
    }
    #s2id_flight-class-select{
        width: 100px;
    }

    .more-detail{
        margin-top: 10px;
    }
    .more-detail .label1{
        text-align: center;
        vertical-align: central;
        font-size: 11px;
        font-weight: bold;
        padding-top: 15px;
    }

</style>
<div id="domMessage" style="display:none;">
    <h1><img width="70" height="70" src="<?php echo base_url("onlinefly/public_html/img/loading.gif"); ?>"/> Uçuşlar getiriliyor</h1>
</div>
<div class="container-narrow">

    <div class="masthead">
        <ul class="nav nav-pills pull-right">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <h3 class="muted">ANKAFLY</h3>
    </div>
</div>

<div class =" container">
    <div class="row fly-search-container">
        <div class="span2">

        </div>

        <div class="span8  fly-search-form form-search" >

            <div class="row fly-direction-option">

                <div class="span2">
                    <label class="radio">
                        <input type="radio" checked="checked" name="flightdirection" value="2"> Gidiş/Dönüş
                    </label>
                </div>
                <div class="span2">
                    <label class="radio">
                        <input type="radio" name="flightdirection" value="1"> Tek Yön
                    </label>
                </div>
                <div class="span2">
                    <label class="radio">
                        <input type="radio" name="flightdirection" value="0"> Çoklu Uçus
                    </label>
                </div>
            </div>

            <div class="row fly-airpot-container">
                <div class="span3 ">
                    <input  id ="boardingairport" type="hidden" name="boardingairport" placeholder="Nereden">
                        <div class="alert-error boardingairpot-alert" style ="display:none">
                            Kalkış Yeri Seçiniz
                        </div>

                </div>

                <div class="span3 offset1 ">
                    <input  id="landingairport" type="hidden" name="landingairport" placeholder="Nereye">
                        <div class="alert-error landingairpot-alert" style ="display:none">
                            İniş Yeri Seçiniz
                        </div>
                </div>
            </div>

            <div class ="row  fly-date-container">
                <div class ="span4">

                    <input type="text" id="go_date" placeholder="Gidiş Tarihi"></input>
                    <div class="alert-error godate-alert" style ="display:none">
                        Gidiş Tarihi Giriniz
                    </div>
                </div>
                <div class ="span4 return-date-container">

                    <input type="text" id="return_date" placeholder ="Dönüş Tarihi"></input>
                    <div class="alert-error returndate-alert" style ="display:none">
                        Dönüş Tarihi Giriniz;
                    </div>
                </div>
            </div>

            <div class="row fly-customer-detail">
                <div class="span1">
                    <span class="label label-info">Yetişkinler</span>
                    <div class="selectBox">
                        <select name="yetiskinNumber" id="yetiskinNumber">
                            <option value="0" >0</option>
                            <option value="1" selected="selected">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                        </select>
                    </div>
                </div>
                <div class="span1">
                    <span class="label label-info">Çocuklar</span>
                    <select name="cocukNumber">
                        <option value="0" selected="selected" >0</option>
                        <option value="1" >1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                    </select>

                </div>

                <div class="span1">
                    <span class="label label-info">Bebekler</span>
                    <select name="bebekNumber">
                        <option value="0" selected="selected" >0</option>
                        <option value="1" >1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                    </select>

                </div>



            </div>
            <div class="row fly-date-options ">
                <div class="span2">
                    <label class="radio">
                        <input type="radio" name="dateOption"  value="1" checked="checked"/>Seçilmiş Tarihler</label>
                    <label class="radio">
                        <input type="radio" name="dateOption"  value="2" />
                        +/-3 gün
                    </label>
                </div>

            </div>
            <div class="row more-detail">
                <div class="span2">
                    <div class="flight-class-container">
                        <span class="flight-class-label  label1"> Sınıf :</span><span class="flight-class-input"> <select id="flight-class-select">

                                <option value="Economy">Economy</option>
                                <option value="Premium Economy">Premium Economy</option>
                                <option value="Business">Business</option>
                                <option value="First">FirstClass</option>
                                <option value="all" selected="selected">Hepsi</option>
                            </select></span>
                    </div>
                </div>

                <div class="span2">
                    <div class="flight-type-container ">
                        <span class="flight-type-label label1"> Uçuş tipi :</span><span class="flight-type-input"> <select id="flight-type-select">
                                <option value="1">Direk</option>
                                <option value="all" selected="selected">Hepsi</option>
                            </select></span>
                    </div>
                </div>

            </div>
            <div class="row fly-searc-button">
                <div class="span6 offset6">
                    <a id="searchButton" class="btn  btn-info" href="#">
                        <i class="icon-search icon-1x ">Uçus Ara</i> </a>

                </div>
            </div>
        </div>

        <div class="span2">

        </div>
    </div>
</div>



