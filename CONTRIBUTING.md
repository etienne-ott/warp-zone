## TODO-List

1. Bootstrapping happens in index.php, test/bootstrap.php and various files in tools/
always very similarly. This behaviour could be extracted into an own class that then
is used in each files with simple calls like ```$bootstrap->readConfig()``` or similar.
However is is important that this Bootstrap class can't require anything only available
after bootstrapping.

2. File access and HTTP access must be set up properly so no outside user can see sensitive
information like credentials. While this theoretically should be the case already, a proper
analysis/review of the various angles of attack is necessary.

3. The phpunit.xml is probably not set up correctly yet. Currently all developer working on
this project can't integrate the unit tests into their IDE anyway, so this is not a priority but
it would be nice to have for future collaborators. At the moment testing works by calling the
script ```run_tests.sh```

4. The README needs love.

5. A build/install system/script needs to be set up.

6. If/when additional persisted objects are required, it is probably
wise to first think about adding an ORM system or at least a persistence layer that is more
sophisticated.

7. autoload.php of WarpZone should use a generated classmap and also support multiple/internal
classes in one file.

11. There is no logger. Why is there no logger?

12. Slim already provides functionality to turn warnings and errors into catchable exceptions, so
there should be mechanism that catches exceptions that bubble up to the global scope and instead
of printing it to the user, should log these exceptions. The use is then shown a generic error
page. This is necessary so exceptions accidentally showing sensitive data (like database credentials),
are not shown to arbitrary users. It also makes debugging easier when all errors/warnings are logged.

13. The template currently works with a pre-content, content, post-content structure. This is not ideal
and should be replaced by something that allows the custom content output to be buffered and
injected into a general page template.