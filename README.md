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

## Q&A
**Q: How I can customize the push notification welcome message and/or promot modal text.**
A: This extension have only English "en-gb" language folder, so if you want to change it for another language, you can create a language file (for ex: admin/language/your-lang/extension/module/easywebpush.php), copy the content from the en-gb folder, and translate it. Another option is to use the OpenCart Language Editor (Design/Language Editor), there you can change the extention text.

**Q: Auto prompt doesn't work**
A: If the promot has been closed/cancelled once, it will take 24h to re-prompt the modal, this is the default setting and you can change it in settings menu. If you want to test it, open your browser dev application tab, delete the prompt date local storage.

## Roadmap 🚧
- Client
  - [x] Client subscription method/triggers, with different themes (floating bell, auto prompt) to be choosed in admin settings dashboard.
  - [ ] Notify me when price drop/Notify me when product back in stock button options in product page.
- Admin
- [x] Admin settings dashboard that contains:
  - [x] Generating & Editing of VAPID keys.
  - [x] Editing default options of a push notification.
  - [x] Selecting pages that show/trigger subscription button/dialog.
  - [x] Configuring some events to auto-send push notifications (such as on order,on resigter etc...).
  - [x] Some analytics, such as number of subscribers, demographics etc...
  - [x] Campaigns, with segmentation/targeting bulk send, with receiving/errors report analysis.
  - [x] Ability to adjust menifest file info (name, icon, color). Perhaps auto generate on install as well.
  - [ ] Choose bell position (left, right).
  - [ ] Add client event triggers: new product, price drop, sale, back in stock, new content (*/information). Admin event triggers: low stock with custom minimal threshold.
  - [ ] Set/select admin events based on admin group.
  - [ ] Dynamic fields for abandond cart, automate sending with interval and maximum try notify.
  - [ ] PWA: offline pages select, display mode, add to home screen button status with custom trigger setup (prompt, footer button).

## Credits 🙏🏼
Credit acknowledgment to the following awesome resources:
1. [web-push-php](https://github.com/web-push-libs/web-push-php).
2. [GeoIP2-php](https://github.com/maxmind/GeoIP2-php).
3. [Mobile-Detect](https://github.com/serbanghita/Mobile-Detect).

## Contributions 🤝🏼
Pull requests are welcome!

## Support 💚
If you feel like supporting this work and improve it, you may purchase the extension at [OpenCart Marketplace](https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=42866) (still under review), or you may
<a href="https://www.buymeacoffee.com/aldabil21" target="_blank" rel="noopener"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height:40px; width:140px;max-width:100%; display: inline-block; position: relative; top: 15px;" ></a>

