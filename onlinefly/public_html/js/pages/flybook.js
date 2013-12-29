/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function(){
    setDoBookButtonEvent();
});


function setDoBookButtonEvent(){
    $(".md-approve").click(function(){
       if(!validateInputs()){
          return;   
       }
       
        applyBookRequest(applyBookResponseCallBack);
       
    })
}

function applyBookRequest(callback){
    var buildRequest = buildbookRequest();
    var url = "applyBook";
         $.post(url , {bookRequest:JSON.stringify(buildRequest)} ,function(data){
              callback(data.data);
         },"json");
    
}

function applyBookResponseCallBack(response){
    if(response.errorCode != "00000"){
        var createdPnr = null;
        if(response.pnrStatusCode != null){
            createdPnr = response.universalRecord.locatorCode;
            alert("PNR("+createdPnr+")hatalı bir sekilde yaratıldı-"+response.errorDesc);
        }else{
            alert("PNR yaratılamadı-"+response.errorDesc);
        }
        
    }else{
         alert("PNR("+response.universalRecord.locatorCode+") yaratıldı");
    }
    
}
function validateInputs(){
    var flyBookUserInfoValid = true;
    var flyBookPassangerInfoValid = true;
    $(".fly-book-passanger-info-fieldset-alert").hide();
    $(".fly-book-user-info-fieldset-alert").hide();
    $(".fly-book-user-info-fieldset .required input").each(function(){
          var thisInput =  $(this);
          thisInput.removeClass("required-input");
          var thisInputValue = thisInput.val();
          
          if(thisInputValue !== undefined && thisInputValue != null && $.trim(thisInputValue).length > 0){
              
          }else {
            thisInput.addClass("required-input");
            flyBookUserInfoValid = false;
          }
          
    });
    
    $(".fly-book-passanger-info-fieldset .required input").each(function(){
         var thisInput =  $(this);
          thisInput.removeClass("required-input");
          var thisInputValue = thisInput.val();
          
          if(thisInputValue !== undefined && thisInputValue != null && $.trim(thisInputValue).length > 0){
              
          }else {
            thisInput.addClass("required-input");
            flyBookPassangerInfoValid = false;
          }
        
    });
    if(flyBookUserInfoValid === false){
        $(".fly-book-user-info-fieldset-alert").show();
    }
    
    if(flyBookPassangerInfoValid === false){
        $(".fly-book-passanger-info-fieldset-alert").show();
    }
    
    return flyBookPassangerInfoValid & flyBookUserInfoValid;
}

function buildbookRequest(){
    var userInfo  = {};
    var passangers = new Array();
    var bookRequest = {};
    userInfo.gender = $("#user-gender").val();
    userInfo.name = $("input[name=user-name]").val();
    userInfo.lastname = $("input[name=user-lastname]").val();
    userInfo.useremail = $("input[name=user-email]").val();
    userInfo.useremailrepeat = $("input[name=user-email-repeat]").val();
    userInfo.usertel = $("input[name=user-tel]").val();
    userInfo.userceptel = $("input[name=user-ceptel]").val();
    userInfo.usercity = $("input[name = user-city]").val();
    userInfo.usercountry = $("select[name=user-country]").val();
    userInfo.userzipcode = $("input[name=user-zipcode]").val();
     
     
    $(".passanger").each(function(){
        var thisPassanger = $(this);
        var passanger = {};
        passanger.gender = thisPassanger.find(".passanger-gender-input").val();
        passanger.name = thisPassanger.find("input[name=passanger-name-input]").val()
        passanger.lastname = thisPassanger.find("input[name=passanger-lastname-input]").val();
        passanger.birthday = thisPassanger.find(".passenger-birthday-input").val();
        passanger.birthMonth  = thisPassanger.find(".passenger-birtmonth-input").val();
        passanger.birthYear = thisPassanger.find(".passenger-birthyear-input").val();
        passanger.type = thisPassanger.find(".passanger-type-code").html();
        passangers.push(passanger);
       
    });
    bookRequest.userInfo = userInfo;
    bookRequest.passangers = passangers;
    return bookRequest;
    
}