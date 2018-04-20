# Upgrade

`npm update` in this folder.  
Delete the `node_modules/` folder if an error occures.

# Compile commands

## LOCAL (No minification, no uglification, faster)

Command: `gulp`

Run this command to work on your files - it will do one compilation and will then watch all files.

Command: `gulp dist`

Run this command to just compile you files without watching them.

## PROD (Minification, Uglification, Slow)

Command: `gulp dist --env prod`

This command will compile your files and minify / uglify them - this task is really slow, only use it as the last command before pushing.

(You can also watch all files with `gulp --env prod` to work with minified/uglified files – not recommended tho)