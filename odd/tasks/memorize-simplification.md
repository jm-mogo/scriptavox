Feature: memorize-simplification. Objective: make Christians able to memorize one verse or a whole chapter easily, then go back and repeat it.
Branch: main (uncommitted work; create a feature branch before the first commit). Delivery: ask-on-risk. Test runner: php artisan test.

## Specs
- S1: Users are Christians who memorize Scripture alone. Memorizing one verse or a whole chapter must be easy, and they must be able to go back and repeat.
- S2: Start screen (replaces Dashboard, Explorer, and My Verses): choose a book and chapter, or add a single verse to the list.
- S3: Memorize screen: show the text, then hide words progressively as the user repeats it. Works for one verse or a whole chapter. The user can go back to any earlier verse.
- S4: Repeat screen: a simple daily list of due items using the existing spaced-repetition logic. Each item opens Memorize.
- S5: Progress view: streak and verses learned. Replaces Stats.
- S6: Remove the separate Practice, Review, Explorer, and Stats pages once their replacements exist.
- S7: Bible text comes from API.Bible. Cache whole chapters server-side, store the text exactly as provided, refresh every 14 days, purge anything older than 30 days, purge all on account or plan termination within 72 hours, and run FUMS tracking on each served chapter. The local seed (BibleSeeder) is removed only after the API source is working.
- S8: Verse text is never altered or stripped.
- S9: Users memorize a range of verses (book, chapter, from-verse, to-verse), not only a whole chapter. A range can be saved to the user's account and resumed later, keeping its hide level.

## Tasks
- T1 | S3 | route: inline | status: pending | commit: -
- T2 | S2 | route: inline | status: pending | commit: -
- T3 | S4 | route: inline | status: pending | commit: -
- T4 | S5 | route: inline | status: pending | commit: -
- T5 | S6 | route: inline | status: pending | commit: -
- T6 | S7, S8 | route: inline | status: blocked (API key and FUMS snippet) | commit: -

## Log
- L1 (2026-10-28, verbatim from user): "The users are christians, that want to memorize, it has to be easy to memorize 1 verse, or a chapter go back, and repeat" and "yes" (approved direction).
- L2: Current pages found: Dashboard, Explore, MyVerses, Practice, Review, Stats. Current verse text lives in the local verses table.
- L3: Next step: T1 Memorize screen on the existing verse data, behind a source interface so the API source can replace it in T6.
- L4 (user decision): voice recitation stays the default through the browser Web Speech API (Chrome/Edge). Browsers without it get a typed-recitation fallback. Option of in-browser Whisper deferred.
- L6 (user decision): keep the current Anki-style spaced repetition for Repeat (T3) while a better option is considered. Simplifying Repeat is deferred and out of scope for now.
- L7: API key and base URL saved in .env (API_BIBLE_KEY, API_BIBLE_BASE_URL). T6 still waits on the FUMS snippet from the API.Bible docs.
- L8: T1 chapter slice done. MemorizeController@chapter, route /memorize/{book}/{chapter}, Memorize/Chapter.vue with 0/25/50/75/100% hiding and tap-to-reveal. Tests: tests/Feature/MemorizeChapterTest.php (3 passing). Full suite 31 passing. Stored verse text is never modified; hiding is presentation only. Still open for T1: single-verse memorize path and removing the old Practice flow (T5).
- L9 (user request, verbatim): "maybe i dont want to memorize a whole chapter but a range of verses this has to be saveable for the user so he can come back later and keep going". Added S9.
- L10: S9 done. Table memorize_passages (user, book, chapter, verse_from, verse_to, hide_level, unique range). Routes /passages (list, save) and /passages/{id} (show, resume, save hide level). Ownership enforced (404 for other users). Shared component components/app/MemorizeVerses.vue used by the chapter page and the saved-passage page. Chapter page has a from/to picker and a Guardar pasaje button. Tests: tests/Feature/MemorizePassageTest.php (6 passing). Full suite 37 passing. Single verse = range with from equal to to.
- L11: T2 done. Dashboard is now the Start screen: book and chapter picker (Memorizar button to /memorize/{book}/{chapter}), recent saved passages, and a Repetir link for due reviews. DashboardController passes books with chapter_count, passages, review_count. Sidebar: Inicio and Mis pasajes added. Tests: tests/Feature/StartScreenTest.php (2 passing). Full suite 39 passing. Single-verse add is covered by a range where from equals to.
- L12 (user request, verbatim): "i would like to recite whole chapter or ranges of verses, instead of 100% ocult, since it makes no sense, put a recite button". Removed the 100% hidden level (UI and server validation). Added a Recitar button in MemorizeVerses.vue that runs RecitationEngine on the displayed range as one passage. Hidden text stays hidden while reciting. Tests: 100% rejected in MemorizePassageTest. Full suite 40 passing.
- L13 (user request): hovering a hidden word shows it. Tapping still toggles the reveal (for touch screens). Implemented in MemorizeVerses.vue with a hovered key that does not change the saved reveals. Build passes; full suite 40 passing. Not yet checked in a browser.
- L14 (user request): results per verse. Spoken words are aligned to the original; each verse shows OK, partial, or a percentage; consecutive fully missed verses collapse into "Versículo N omitido" or "Versículos N-M omitidos". Implemented in RecitationEngine.vue (verses prop). MemorizeVerses.vue passes the verses.
- L15 (user request): the visual comparison shows the original punctuation and capitals. Original tokens are colored by alignment (match green, miss red). Checked with a Node script on a sample verse. Build passes; full suite 40 passing. Not yet checked in a browser.
- L16 (user request): recited or partly recited verses show the verse text with word-level errors (matched green, missed red with line-through). Fully missed verses stay as the short "omitido" line. Implemented in RecitationEngine.vue (tokensByVerse, verse result lines).
- L17 (user request): the verse keeps the original punctuation and capitals. Stored text and result tokens already keep them. The hidden-word mask dropped punctuation and capitals, so maskWord now replaces only letters and digits with underscores and keeps punctuation. Build passes; full suite 40 passing. Not yet checked in a browser.
- L18 (user request): Mis pasajes had no way to add a passage. Added an "Añadir pasaje" card on that page: book, chapter, and from/to verse selectors, then Guardar pasaje. MemorizePassageController::index now passes books with chapter_count and verses per chapter. Test added (8 passing in MemorizePassageTest). Full suite 41 passing.
- L19 (user decision): keep the local RVR1960 text. The API.Bible switch (T6) is dropped for now. Reason: RVR1960 is not available on the account (only Reina Valera 1909 is listed), and the user needs RVR1960 for the app to be useful. Open risk: the local text is still copyrighted; a license from the publisher is needed before public release. S7 and S8 do not apply until this changes. The API key and base URL stay in .env, unused. The base URL typo (/v1o) was fixed.
- L5: T1 partial. RecitationEngine.vue unsupported-browser branch now offers a typed recitation with the same accuracy check. npm run build passes. Still missing: progressive word hiding, chapter-level memorize, and the Memorize page replacing Practice.
