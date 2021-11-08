<?php
// Heading
$_['heading_title']    = 'Easy Web Push';

// Side menu
$_['menu_title']        = 'Easy Web Push';
$_['menu_dashboard']    = 'Dashboard';
$_['menu_campaign']     = 'Campaigns';
$_['menu_subscribers']  = 'Subscribers';
$_['menu_setting']      = 'Settings';

// Text
$_['text_extension']              = 'Extensions';
$_['text_success']                = 'Modified successfully!';
$_['text_edit']                   = 'Edit Easy Webpush';
$_['text_saved_success']          = 'Saved Successfuly';
$_['text_vapid_keys']             = 'VAPID Keys';
$_['text_vapid_keys_intro']       = '<span style="color:red;"><strong>Important:</strong></span> These VAPID keys are auto-generated on installation, and even when you uninstall/reinstall this extension, it will preserve the keys in the database and re-use it on future installs. This is because VAPID keys should be generated <strong>once and only once</strong> in the entire life-time of your app, changing them will cause you to <strong>lose all your subscribers</strong>. Therefore it\'s not recommended to edit them, it\'s better to save them somewhere safe on you machine in case something else happen to your data if for example you migrate your app somewhere else.';
$_['text_vapid_keys_intro1']      = 'Unless you <strong>know what you doing</strong>, or you did have previous keys you want to use, you can click the button below to edit/generate new keys.';
$_['text_vapid_keys_edit']        = 'I know what I\'m doing, I want to edit them';
$_['text_vapid_generate']         = 'Auto Generate';
$_['text_subscribed_alert']       = '<strong>Great!</strong> You are subscribed on this device, and you should receive admin push notifications.';
$_['text_unsubscribed_alert']     = '<strong>Warning:</strong> You are not subscribed on this device and you won\'t receive admin notifications.';
$_["text_subscription_success"]   = "You've Subscribed Successfully";
$_["text_unsubscription_success"] = "You are unsubscribed! You will not receive notfications anymore.";
$_["text_unsubscription_error"]   = "Fail to unsubscribe, refresh and try again.";
$_["text_subscribers_list"]       = "Subscribers";
$_["text_anonymous"]              = "Anonymous";
$_["text_send_subscription"]      = "Send Subscription";
$_["text_send_selected"]          = "Selected";
$_["text_preview"]                = "Preview";
$_["text_delete_success"]         = "Successfully deleted (%d) %s";
$_["text_no_subscriptions"]       = "No Subscriptions Found...";
$_["text_campaigns_list"]         = "Campaigns";
$_["text_no_campaigns"]           = "No Campaigns Found...";
$_["text_new_campaign"]           = "New Campaign";
$_["text_campaign_info"]          = "Campaign Info";
$_["text_campaign_message"]       = "Campaign Message";
$_["text_total_targeted"]         = "Total Subscribers";
$_["text_campaign_sent"]          = "Successfully sent the campaign: %s";
$_["text_campaign_stat"]          = "Campaign Stats";
$_["text_no_data"]                = "No Data...";

// Setting nav
$_['nav_setting_generals']        = 'Generals';
$_['nav_setting_vapid']           = 'VAPID';
$_['nav_setting_pwa']             = 'PWA';
$_['nav_setting_events']          = 'Events';

// Labels
$_["label_name"]                 = "Name";
$_["label_email"]                = "Email";
$_["label_telephone"]            = "Telephone";
$_["label_device"]               = "Device";
$_["label_total"]                = "Total";
$_["label_success"]              = "Success";
$_["label_failed"]               = "Failed";
$_["label_opened"]               = "Opened";
$_["label_na"]                   = "Not Applicable";
$_["label_date_added"]           = "Date Added";
$_["label_target"]               = "Target";
$_["label_filter2"]              = "Who";
$_["label_filter3"]              = "is";
$_["label_total_campaigns"]      = "Campaigns";
$_["label_total_subscribers"]    = "Subscribers";
$_["label_admin_subscribers"]    = "Admins Subscribed";
$_["label_success_rate"]         = "Success Rate";
$_["label_failed_rate"]          = "Failed Rate";
$_["label_opened_rate"]          = "Opened Rate";
$_["label_devices"]              = "Subscriber's Device";
$_["label_subscriber_country"]   = "Subscriber's Location";


// Filter
$_["filter_all"]            = "All";
$_["filter_subscribers"]    = "Subscribers";
$_["filter_registered"]     = "Is registered";
$_["filter_anonymous"]      = "Is Not Registered";
$_["filter_device"]         = "Device";
$_["filter_abandoned_cart"] = "Has Abandoned Cart";
$_["filter_customer_group"] = "Customer Group";
$_["filter_country"]        = "Country";

// Entry
$_['entry_status']               = 'Status';
$_['entry_on']                   = 'On';
$_['entry_off']                  = 'Off';
$_['entry_bell_status']          = 'Subscription Bell';
$_['entry_autoprompt_status']    = 'Auto Prompt';
$_['entry_autoprompt_delay']     = 'Auto Prompt Delay';
$_['entry_autoprompt_delay_helper']  = 'In seconds';
$_['entry_autoprompt_reinit']    = 'Re-prompt after/Hour';
$_['entry_autoprompt_reinit_helper']  = 'When user cancel auto prompt subscription, when you want to re-promot again? Time in hours';
$_['entry_prompt_text']          = 'Prompt Content';
$_['entry_primary_color']        = 'Primary Color';
$_['entry_primary_color_helper'] = 'Color of floating bell & prompt primary button';
$_['entry_prompt_logo']          = 'Prompt Logo';
$_['entry_prompt_routes']        = 'Shows in';
$_['entry_events_for_admin']     = 'Send push to admin when customer';
$_['entry_events_for_customer']  = 'Send push to customer when admin';
$_['entry_register']             = 'Register';
$_['entry_order']                = 'Complete Order';
$_['entry_review']               = 'Write Review';
$_['entry_order_status']         = 'Update Order Status';
$_['entry_pwa_short_name']       = 'Short Name';
$_['entry_pwa_short_name_helper'] = 'Short version of Name property, which is name of the web application displayed to the user if there is not enough space to display "Name"';
$_['entry_pwa_name']             = 'Name';
$_['entry_pwa_name_helper']      = 'The name of web application displayed to the user as label for the icon';
$_['entry_pwa_description']      = 'Description';
$_['entry_pwa_description_helper'] = 'Simple description explains what the application does';
$_['entry_pwa_background_color']   = 'Background color';
$_['entry_pwa_background_color_helper'] = 'Background color for the application page to display before the CSS is loaded';
$_['entry_pwa_theme_color']        = 'Theme color';
$_['entry_pwa_theme_color_helper'] = 'Theme color for the application. This affects app color depends on the OS';
$_['entry_pwa_icons']              = 'App Icons';
$_['entry_pwa_icons_helper']       = 'Notice: Be aware of the icon "minimum safe zone", use maskable images to better display your icon. This service is a good help to test your icons: <a href="https://maskable.app/editor" target="_blank" rel="noreferrer">maskable.app</a>';
$_['entry_pwa_icon']               = 'App Icon';
$_['entry_vapid_public']           = 'Public Key';
$_['entry_vapid_private']          = 'Private Key';
$_['entry_title']                  = 'Title (20-40 letter best)';
$_['entry_message']                = 'Message (20-80 letter best)';
$_['entry_image']                  = 'Image (2:1 Landscape - min width 512px best)';
$_['entry_icon']                   = 'Icon (1:1 - min width 256px best)';
$_['entry_second_action']          = 'Add Second Action';
$_['entry_action_title']           = 'Action Title';
$_['entry_action_link']            = 'Action Link';

// Buttons
$_['button_subscribe']             = 'Subscribe';
$_['button_unsubscribe']           = 'Unsubscribe';
$_["button_send_to_selected"]      = "Send to Selected";
$_["button_send"]                  = "Send";
$_["button_create_campaign"]       = "Create Campaign";

// Error
$_['error_permission']              = 'Warning: You do not have permission!';
$_['error_swjs_not_found']          = 'Critical error: sw.js or serverworker.js files not found or not in the correct path.';
$_['error_render_template_not_found'] = 'Critical error: Target render template not found.';
$_['error_composer_not_found']      = 'Couldn\'t detect php composer to install required libs.';
$_['error_php_version']             = 'Your PHP version should be 7.2 or heigher. Your PHP version is: %s';
$_['error_fail_install_webpush_lib'] = 'Somthing wrong when installing webpush libs.';
$_['error_gmp_not_loaded']           = 'gmp extension is not loaded, it is required for sending push notifications with payload and/or VAPID authentication. You can fix this in your php.ini';
$_['error_prompt_text']             = 'Prompt text must be between 1 and 255 letter.';
$_['error_save_fail']               = 'Faild to save: Check form errors.';
$_['error_string_length']           = 'Should be between %s and %s letters';
$_['error_vapid']                   = 'Wrong format';
$_["error_push_blocked"]            = "Push Notifications are blocked by your browser. Try to enable it";
$_["error_browser_unsupported"]     = "Unfortunately, your browser does not support Push Notifications";
$_["error_subscription_failed"]     = "Faild to subscribe. Please refresh and try again";
$_["error_empty_selected"]          = "Nothing was selected";
$_["error_action_link"]             = "Use a valid link";
$_['error_send_fail']               = 'Fail to send: Check form errors';
$_['error_no_campaign_receivers']   = 'Fail to send: No receivers specified';


// Report
$_["report_send_push_success"]    = "Successfully Sent (%d) messages";
$_["report_send_push_fail"]       = "Faild to send (%d) messages. %s";
$_["report_send_push_unsub"]      = "(%d) of them where deleted due to unsubscribed reason.";