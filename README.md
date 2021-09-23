# Easy Webpush 📨

### Webpush Notification & Progressive Web App for OpenCart 3.X.X

This is a basic setup, packaged based on OpenCart event system, providing webpush ability to OpenCart websites.

> ###### ⚠️ For now this is just a setup for testing purpose, not any admin/dashboard interface been made.

## Requirements

Since this package depends on [minishlink/web-push](https://github.com/web-push-libs/web-push-php) to send push notifications, so your enviroment required to be:

1. PHP version >= 7.2.
2. gmp extension.
3. SSL (duh!).

#### Pros

1. Using event system. And will continue all development using the event system.
2. ... Nothing more to mention now, untill start implementing some of the roadmap section.

#### Cons

1. Some hosting providers PHP setup may not have gmp extension, and you may not have controll to install it.

###Roadmap

- [ ] Client subscription method/triggers, with different themes (Floating bell, bottom sliding paper, dialog/modal) to be choosed in admin settings dashboard.
- [ ] Admin settings dashboard that contains:
- [ ] Generating & Editing & Testing of VAPID keys.
- [ ] Editing default options of a push notification.
- [ ] Selecting pages that show/trigger subscription button/dialog.
- [ ] Configuring some events to auto-send push notifications (such as on order, on abandoned cart more than 24h, on resigter etc...).
- [ ] Some analytics, such as number of subscribers, demographics etc...
- [ ] Campaigns, with segmentation/targeting bulk send, with receiving/errors report analysis.
- [ ] Ability to adjust menifest file info (name, icon, color). Perhaps auto generate on install as well.

This was originally uploaded at [opencart-webpush-pwa](https://github.com/aldabil21/opencart-webpush-pwa) repo, more about implemetation of sending a push notification there.

> ⚠️ This is more or less like a project still inside an eggshell, and perhaps may take long time to see the light. Don't expect any fancy & ready to use setup here.
