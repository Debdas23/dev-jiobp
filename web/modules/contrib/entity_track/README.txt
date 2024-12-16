Entity Track
============

This module provides an API to track entities. When working with complex
content, it is important for editors to get information about entities they are
working with. Some examples of this are:

- Track complex relations between entities (how/where is an entity being used).
- Track misconfigurations (keep track of external IDs in content to get
  notified when external APIs remove the referenced ID).
- Track (internal/external) links in content (get notified when an internal or
  external link no longer exists).

How does it work?
============

To use the API, modules can simply provide a custom entity type and 1 or more
tracking plugins. A tracking plugin can "subscribe" to 1 or more field types.
When content is being saved, all tracking plugins are called for each field of
the type they are subscribed to. The plugin can fetch the information it needs
from the field data, and store the information it wants to track in the custom
entity.

The tracked information can be displayed for anyone that needs the information.
An example of this would be the Entity Usage module, which provides a tab for
entities to show how/where the entity is being used by other entities.

the entity_track_test test module provides a simple example of how to build a
module to track information in entities.

Batch update
============

The module provides a tool to erase and regenerate all tracked information about
usage of entities on your site.
Go to the URL /admin/config/entity-track/batch-update in order to start the
batch operation.

Tracking using background process
============
The module supports tracking entities via a background process. When the entity
is tracked via a background process, the tracking information will be updated
after the page was loaded in the background.

Since this is for advanced users only, this setting is not exposed in the UI.
This can be enabled by importing config, setting the value programmatically
with custom code, or overriding it at runtime by adding the following line to
your settings.php file:

$config['entity_track.settings']['background_tracking'] = TRUE;

Project page and Online handbook
================================

More information can be found on the project page:
  https://www.drupal.org/project/entity_track
