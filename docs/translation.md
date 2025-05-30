# Translation

Translation is very basic, we don't need an overengineered library to handle 4 messages.

You can set the language of your choice in the configuration file:
```
    'lang' => 'fr',
```
Default is `en`.

## Custom messages

You may define custom messages for your weird language in the configuration file:
```
    'messages' => [
        'es' => [ /* your messages here */],
    ]
```
The default messages are:
- `previous`
- `next`
- `previous_page`
- `next_page`
- `aria-pagination`: used in aria label for pagination nav

You can use as many messages as you need, the appropriate ones will be injected according to the language defined.
