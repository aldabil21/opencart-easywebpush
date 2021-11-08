# Easy Web Push 📨
Web Push Notification & Progressive Web App for OpenCart 3.x.x

## Installation 📦
Download this repo and copy the content into folder named `easywebpush.ocmod` then zip it, you would have `easywebpush.ocmod.zip` file, upload it in OpenCart installer page.
After installation, make sure you enable the extension permissions at the `User Groups` page.

## Requirements ⚙️
1. PHP version >= 7.2.
2. gmp extension.
3. SSL.

#### Pros ✔️
1. Own your data. No limiations.
2. No OCMOD or files override, 100% depends on event system.
3. No blocking code, no external API requests, all configurations at your own hosting.
4. This extension try its best to generate VAPID keys once only, even if you uninstall it, it will keep a backup copy of the VAPID keys in the database. If for example you migrate hosting provider, its safe to reinstall the extension there and it will use the initial generated VAPID keys, unless you db is wiped out, that's another story.
5. Create campaigns and observe push messages success, fail and click rate.
6. Pretty easy to use (maybe my opinion 😅, let me know if it's not).

#### Cons ⚠️
1. Some hosting providers PHP setup may not have gmp extension, and you may not have controll to install it.
2. You can target customers with abandoned cart, but in general message. It would be better to tailor message with dynamic fields according to customer's own cart, and to automate cron job for that.
3. Campaign segmentation/targeting filters are way too simple and limited.
4. Events trigger selection are also way too simple and limited.


## Roadmap 🚧
- [x] Client subscription method/triggers, with different themes (floating bell, auto prompt) to be choosed in admin settings dashboard.
- [x] Admin settings dashboard that contains:
  - [x] Generating & Editing of VAPID keys.
  - [x] Editing default options of a push notification.
  - [x] Selecting pages that show/trigger subscription button/dialog.
  - [x] Configuring some events to auto-send push notifications (such as on order,on resigter etc...).
  - [x] Some analytics, such as number of subscribers, demographics etc...
  - [x] Campaigns, with segmentation/targeting bulk send, with receiving/errors report analysis.
  - [x] Ability to adjust menifest file info (name, icon, color). Perhaps auto generate on install as well.

More to be decided according to feedbacks.

## Credits 🙏🏼
Credit acknowledgment to the following awesome resources:
1. [web-push-php](https://github.com/web-push-libs/web-push-php).
2. [GeoIP2-php](https://github.com/maxmind/GeoIP2-php).
3. [Mobile-Detect](https://github.com/serbanghita/Mobile-Detect).

## Contributions 🤝🏼
Pull requests are welcome!

## Support 💚
If you feel like supporting this work and improve it, you may purchase the extension at [OpenCart Marketplace](https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=42866) (still under review), or you may
<a href="https://www.buymeacoffee.com/aldabil21" target="_blank" rel="noopener"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 50px !important;width: 180px !important;" ></a>

