## warp-zone

A startpage for people who like to cram as many links as possible on one page. Add new links in one simple form within the browser.
The source files are released in two tracks. One track with simpler functionality and no fancy javascript, the other track using 
every js trick in the book.

### Requirements(~) & Recommendations(+)
* ~ PHP >= 5.0, though it might work with previous versions
* ~ Working webserver such as Apache
* + Properly configured .htaccess and file-access rights
* + Backup system for the data files

### Features implemented(~) and planned(+) in no particular order
* ~ Display of existing entries
* ~ Form to create new entries/sections
* ~ Sorting of entries and sections by priority
* ~ Theme-picker with various color/size themes
* + In-page editing of entries
* + In-page editing of entry/section order
* + Option to track and priorize entries by how often they are clicked
* + Multiple selectable warp-zones per project

### Installation and use
1. Execute ```git clone git@github.com:eott/warp-zone``` to clone the repository into a folder of your chose. Ideally this is a subfolder accessible on your webserver. Alternatively, you can use the github functionality to download the source files manually. Should be around here somewhere (if you are reading this on github).
2. Open the file ```generate_index.php``` in a browser or execute the script via php in a shell.

    Warning: A file called ```index.html``` will be created (among others). Be sure not to accidentally overwrite any existing files.

3. You should be automatically redirected to the generated index file. This is now your own (empty) warp zone.
4. Create new entries by entering the data into the form within the page (in the header). Since you have no entries yet, there will be no sections to choose from, so just enter the name of a new section as well. Entries without a section will be put into the default section "Read later".
5. Existing entries and sections are saved in the files ```entries.csv``` and ```sections.csv``` respectively. You can edit these files directly and apply the changes with the ```Rebuild``` button. For now, changing the priority of entries and sections must be done by editing the files.
6. If you have a lot of entries it is advisable to regularly create backups of the files or schedule automatic backups. You should also make backups when downloading a new version of warp-zone to avoid having your data deleted accidentally on a rebuild.
7. Now, slowly turn your warp zone into a complete index of the web and don't forget to be awesome.

### Releases
The source is released in two tracks. The tortoise track has simpler, robust functionality. This is intended for developers who like things simple and don't need fancy UX gizmos. The hare track is always up to date and tries to be as convenient as possible, at the expense of simplicity.

Hare: Latest release [v1.1.1](https://github.com/eott/warp-zone/releases/tag/v1.1.1-hare)

Tortoise: Latest release [v1.1](https://github.com/eott/warp-zone/releases/tag/v1.1-tortoise)
