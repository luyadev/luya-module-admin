var observeLogin=function(e,i,o,r){$(e).submit(function(e){$("#errorsContainer").hide(),$('.login-btn[type="submit"]').attr("disabled",!0),$(".login-spinner").show(),$(".login-btn-label").hide(),e.preventDefault(),$.ajax({type:"POST",url:i,data:$(this).serialize(),error:asyncError,success:function(e){$('.login-btn[type="submit"]').attr("disabled",!1),$(".login-spinner").hide(),$(".login-btn-label").show();var i=e.refresh,o=e.errors,r=e.enterSecureToken;e.enterTwoFaToken&&($("#twofaForm").show(),$("#loginForm").hide()),0<o.length&&($("#errorsContainer").html(errorsToList(o)),$("#errorsContainer").show(),$("#password").val(""),$("#email").focus()),r&&($("#secureForm").show(),$("#loginForm").hide()),i&&($("#forgot").hide(),$("#errorsContainer").hide(),$("#success").css("visibility","visible"),$("#secureForm").hide(),$("#loginForm").hide(),$(".login-logo").hide(),location.reload())},dataType:"json"})}),$("#secureForm").submit(function(e){$("#errorsSecureContainer").hide(),$('.login-btn[type="submit"]').attr("disabled",!0),$(".login-spinner").show(),$(".login-btn-label").hide(),e.preventDefault(),$.ajax({type:"POST",url:o,data:$(this).serialize(),error:asyncError,success:function(e){$(".login-spinner").hide(),$(".login-btn-label").show(),$('.login-btn[type="submit"]').attr("disabled",!1);var i=e.refresh,e=e.errors;0<e.length&&($("#errorsContainer").html(errorsToList(e)),$("#errorsContainer").show()),i&&($("#forgot").hide(),$("#errorsContainer").hide(),$("#success").css("visibility","visible"),$("#secureForm").hide(),$("#loginForm").hide(),$(".login-logo").hide(),location.reload())},dataType:"json"})}),$("#twofaForm").submit(function(e){$("#errorsSecureContainer").hide(),$('.login-btn[type="submit"]').attr("disabled",!0),$(".login-spinner").show(),$(".login-btn-label").hide(),e.preventDefault(),$.ajax({type:"POST",url:r,data:$(this).serialize(),error:asyncError,success:function(e){$(".login-spinner").hide(),$(".login-btn-label").show(),$('.login-btn[type="submit"]').attr("disabled",!1);var i=e.refresh,e=e.errors;0<e.length&&($("#errorsContainer").html(errorsToList(e)),$("#errorsContainer").show()),i&&($("#forgot").hide(),$("#errorsContainer").hide(),$("#success").css("visibility","visible"),$("#twofaForm").hide(),$("#loginForm").hide(),$(".login-logo").hide(),location.reload())},dataType:"json"})}),$("#abortToken").click(function(e){$(".spinner").hide(),$(".submit-icon").show(),$("#errorsContainer").hide(),$("#secureForm").hide(),$("#loginForm").show(),$("#forgot").show(),$("#success").css("visibility","hidden"),$(".login-spinner").hide(),$(".login-btn-label").show(),$('.login-btn[type="submit"]').attr("disabled",!1)}),$("#abortTwoFa").click(function(e){$(".spinner").hide(),$(".submit-icon").show(),$("#errorsContainer").hide(),$("#twofaForm").hide(),$("#loginForm").show(),$("#forgot").show(),$("#success").css("visibility","hidden"),$(".login-spinner").hide(),$(".login-btn-label").show(),$('.login-btn[type="submit"]').attr("disabled",!1)})},asyncError=function(e){$("#errorsContainer").html(e.statusText),$("#errorsContainer").show(),$(".login-spinner").hide(),$(".login-btn-label").show(),$('.login-btn[type="submit"]').attr("disabled",!1)},errorsToList=function(e){var i,o="<ul>";for(i in e)o=o+"<li>"+e[i].message+"</li>";return o+="</ul>"},checkInputLabels=function(){function e(e){var i=e.val()?e.val():"",o="rgb(250, 255, 189)"===window.getComputedStyle(e[0],null).getPropertyValue("background-color");1<=i.length||!0==o?e.addClass("is-not-empty").removeClass("is-empty"):e.addClass("is-empty").removeClass("is-not-empty")}var i=$(".login-input");i.on("keyup paste change click",function(){e($(this))}),i.each(function(){e($(this))})};