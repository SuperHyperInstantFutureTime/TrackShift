A music royalties data aggregator
=================================

We make releasing music easier.

We are DIY artists, label people, software developers and music consumers who are building tools to give indies superpowers in the age of big data.

"Giving indies superpowers in the age of big data."

Dev todo
--------

Notes for Wednesday: the database speedup is immense. From 6 minutes to about 15 seconds.
But there's still more to do, and ideally it should all be done within 1 second.
So, here's how:

- [x] When the file is uploaded, just store it in the Upload table.
- [x] Introduce a new field, Upload.processedUsages
- [x] In a background script, loop over all uploads that are not processed and extract their usages (then mark as processed)
- [ ] Introduce another new field, Usage.processed
- [ ] In another background script, loop over all usages that are unprocessed, finishing the job here.
- [ ] The usage processor needs to match products and artists - rather than doing this individually in a loop, lookup the unique artist/product first, to cache the IDs (or create new ones), then it's possible to insert UsageOfProduct rows on bulk!
- [ ] Then optimise further with a profiler. Ideally, a very large import should be completed before the page has chance to reload.
- [ ] If a spinner is necessary, it should be put onto the three-checkbox page. It's also possible to know how many usages are left to process, so an ACTUAL progress bar is possible.

Setup guide
-----------

TODO: From scratch for Linux, Windows and Mac.

Running locally
---------------

TODO.

Writing/running tests
---------------------

TODO.
