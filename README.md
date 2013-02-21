Bankcracker
===========

Bankcracker lets you create entries in multiple channels from a single front end Safecracker form submission in ExpressionEngine.

This is currently very basic and has not been extensively tested... Use at your own risk!

It has not yet been tested for editing functionality either... only for submitting new entries.

Usage
===========

Within a normal Safecracker form (or third-party forms that use Safecracker behind the scenes, such as Profile:Edit etc), add fields using the naming convention *channel_n:field_name*, where n is the channel ID and field_name is the name of the field. All the normal Safecracker rules apply, so ensure your member group has permission to publish in all the relevant channels, and be sure to include all the required fields. The fields of the channel that is specified in your Safecracker tag parameters do not need the 'channel_n:' prefix.

Example
===========

  	{exp:safecracker channel="your_main_channel" return="anything"}
			<label for="title">Title</label>
			<input name="title" type="text">

			<label for="url_title">URL</label>
			<input name="url_title" type="text">
			
			<!-- extra channel fields -->
			<input type="text" name="channel_5:title">
			<input type="text" name="channel_5:url_title">
			<input type="text" name="channel_5:custom_field_name">

			<input type="text" name="channel_4:title">
			<input type="text" name="channel_4:url_title">
			<input type="text" name="channel_4:custom_field_name">

			<button type="submit">Submit</button>
	{/exp:safecracker}

Extra Dev Hook: bankcracker_end($safecracker)
============================================

Bankcracker triggers a safecracker submission for every new channel entry it needs to create. Therefore it's important to note that any other extensions that make use of the safecracker_submit_entry_end/start hooks will also get triggered multiple times - possibly leading to unpredictable consequences. To alleviate this, Bankcracker provides its own bankcracker_end extension hook that can be used for further processing after all entries have been added. Just like the safecracker_submit_entry_end hook, it passes the Safecracker object along with it.

Change log
===========

* v.0.6: Fix to ensure Safecracker's <code>dynamic_title</code> parameter only applies to the main entry
* v.0.5: Initial Release
