# NoTell

A PocketMine plugin to hide automatically refuse /tell and /msg.

Commands are now saved and reloaded on server restart.

COMMANDS:

/notell : Shows if the current player know if they can use /tell
/notell <player>: Shows if the specified player know if they can use /tell
/notell <player> [on|off]: Turns on/off if the specified player can use /tell
/notell oprecv: Shows if players can use /tell to OP players
/notell oprecv [on|off]: Turns on/off if players can /tell OP players
/notell opsend: Shows if any OP player can use /tell
/notell opsend [on|off]: Turns on/off if OP players can use /tell

CONFIG:
op-recv: true|false         Set true/false if players can /tell OP players
op-send: true|false         Set true/false if OP players can use /tell
player-send:
    <player>:true|false     Set true/false if specified player can use /tell



PERMISSIONS:
notell


