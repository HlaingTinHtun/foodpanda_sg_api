## Environment Setup
I developed this test project with laravel 5.2
- Make the environment setup. I made using homestead
- Enter your vagrant ssh
- Make `composer install` to pull the dependencies.
- Build the database in your local env.
- Make migration `php artisan migrate`

## Explanation I found the link for location filter

In foodpanda website, when a user put a postal code, I found that the parameters (lat,lng,street,houseNumber,postcode,extendedDetailsId and tracking_id) are working depending on what users input.

To filter the restaurants, these all parameters need to pass through.

I found these two link to implement the `restaurant filter` function

To get postcode and address info = `https://www.foodpanda.sg/location-suggestions-ajax?address=postalCodeValue`

To retrieve restaurants list = `https://www.foodpanda.sg/restaurants?lat=1.28545&lng=103.776979&street=Wholesale Centre&houseNumber=13A&extendedDetailsId=01ee3cc14ab841b991a4069dbade058a&tracking_id=01ee3cc14ab841b991a4069dbade058a&postcode=111013`

In my api, it accepts the postal code value and check this postal code is correct format or not. And  use this endpoint `https://www.foodpanda.sg/location-suggestions-ajax?address=postalCodeValue` to find the correct address info such as `lat`, `lang` etc …. for this postal code.

When I get the correct postal code and correct address info, I use this endpoint `https://www.foodpanda.sg/restaurants?lat=&lng=&street=Wholesale Centre&houseNumber=&extendedDetailsId=&tracking_id=&postcode=`
to filter and scrap the restaurants from `foodpanda` website.

And I also implement  another endpoint in my api which scrap the restaurants data from `foodpanda` website and dump this data to my `database`.
Note : In real `API`,  I will run this `database` insert function with `Queue` or `Event`. In this way this function cannot effect much on `API performance` .

## EndPoints to test in project.

Filter the restaurants with postal code
- http://foodpanda.app/restaurants?postcode=123440
If you want to add the data to database , you can use that one
-  http://foodpanda.app/restaurants?postcode=123440&insertdatabase=true