Bankcracker
===========

Bankcracker lets you create entries in multiple channels from a single front end Safecracker form submission in ExpressionEngine.

This is currently very basic and has not been extensively tested... Use at your own risk!

Usage
===========

Within a normal Safecracker form (or third-party forms that inherit the Safecracker object, such as Profile:Edit etc), add fields using the naming convention {channel_n:field_name}, where n is the channel ID and field_name is the name of the field. All the normal Safecracker rules apply, so ensure your member group has permission to publish in all the relevant channels, and be sure to include all the required fields. The channel that is specified in your tag parameters does not need the prefix.

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

Bankcracker uses the safecracker_submit_entry_end extension hook for every new channel entry it needs to create. Therefore it's important to note that any other extensions that also use this hook will get triggered multiple times - possibly leading to unpredictable consequences. To alleviate this, Bankcracker provides its own bankcracker_end extension hook that can be used for further processing after all entries have been added. Just like the safecracker_submit_entry_end hook, it passes the Safecracker object along with it.
