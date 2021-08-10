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
        } else {
			username_var = data['query']['userinfo']['name'];
            $('#username').html( 'Sveiks, '+username_var+'!' );
            $('#login').html( '<a href="../index.php?action=logout" target="_parent">iziet</a>' );
			$('#inputAuthor').val(username_var);
        }
    });
});