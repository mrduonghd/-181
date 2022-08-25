# Marketplace Template Reposotory

## Expected system versions
- ubuntu18.04
- MySQL5.7
- php7.2
- elasticsearch7.6.2

## How to use it

Deploy source code and setup
```
git clone git@gitlab.com:true-mp/mp-template.git [your_project_name]
cd [your_project_name]
chmod 777 -R ./var
chmod 777 -R ./generated
chmod 777 -R ./app/etc
chmod 777 -R ./pub/media
chmod 777 -R ./pub/static
composer update
```

Imstall Magento2 by using following command with options
*You have to fill options before you execute this command!
```
./bin/magento setup:install
--base-url= \
--db-host= \
--db-name= \
--db-user= \
--db-password= \
--admin-firstname= \
--admin-lastname= \
--admin-email= \
--admin-user= \
--admin-password= \
--language=ja_JP \
--currency=JPY \
--timezone=Asia/Tokyo \
--backend-frontname=
```
or
from browser.
Go to http://your_project_url

## Included extension list

### Magento

- Paypal
Magento/Paypal module modified to use it for JP Yen.

### CommunityEngineering JP Localization
https://github.com/magento/magento2-jp

- Kuromoji
(For elasticsearch kuromoji)

### Webkul

- Marketplace
Marketplace extension.
- Marketplace API
Marketplace API extension.
- MarketPlaceBaseShipping
Extension to enable sellers to set Table Rate Shipping for them.
- MarketPlaceAssignProduct
Extension to enable sellers to sell common products.
- MarketPlaceTimeDelivery
Extension to enable sellers to set Delivery Time for them.
- MarketPlaceShipping
Extension to enable sellers to set Shipping Method for them.
