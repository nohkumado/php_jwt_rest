# A php jwt rest base framework 
A base framework to have a basic jwt authenticated rest api for new php projects

For each new project i have to sort out the basic rest api from a previous one to buils up a rest api
to make this happen faster lets build a ready to use skeleton


look at anonlogin.php it inherits from processor, and only adds all the business logic needed, the rest handling 
and jwt generation you call from the parent class.
Anon Login just accepts any kind of login and returns a jwt tag, this is just for illustration purposes, please override
with meaningful code....

how to call/activate this is illustrated in api.php
