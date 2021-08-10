$(document).ready(function(){
    $.ajax({
        type: 'GET',
        url: '../oauth.php',
        data: {action : 'userinfo'}
   })
   .done(function(data){
        if ('error' in data){
            $('#username').html( '' );
            $('#login').html( '<a href="../index.php?action=authorize" target="_parent">ienākt</a>' );
            $('#only_logged').html( '<div class="alert alert-warning" role="alert">Lai pievienotu rakstu, Tev ir <a class="alert-link" href="../index.php?action=authorize" target="_parent">jāielogojas</a>!</div>' );
        } else {
			username_var = data['query']['userinfo']['name'];
            $('#username').html( 'Sveiks, '+username_var+'!' );
            $('#login').html( '<a href="../index.php?action=logout" target="_parent">iziet</a>' );
			
			window.onload = function() {
				$('#inputAuthor').val(username_var);
			}
			
        }
    });
});