window.fbAsyncInit = function() {
  FB.init({
    appId      : '3271767706278150',
    xfbml      : true,
    version    : 'v2.9'
  });
  FB.AppEvents.logPageView();
};

(function(d, s, id){
   var js, fjs = d.getElementsByTagName(s)[0];
   if (d.getElementById(id)) {return;}
   js = d.createElement(s); js.id = id;
   js.src = "//connect.facebook.net/en_US/sdk.js";
   fjs.parentNode.insertBefore(js, fjs);
 }(document, 'script', 'facebook-jssdk')); 


function shareScore(score) {
  FB.ui({
    method:'share',
    href: 'https://google.com',
    hashtag: "#MILoMejor",
    quote: "Mi puntuacion: " + score
  }, function(response){});
}
