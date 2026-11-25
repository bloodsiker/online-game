// Изучение вороха у Мудрого Финко: модальное окно с прогресс-баром на 1 минуту.
// Таймер ведёт сервер (start → complete по токену), окно лишь показывает прогресс и результат.
(() => {
    'use strict';
    const root = document.querySelector('[data-study]');
    if (!root || root.dataset.initialized) return;
    root.dataset.initialized = '1';

    const config = JSON.parse(root.dataset.config);
    const find = name => root.querySelector('[data-study-' + name + ']');
    const show = (name, visible) => { find(name).hidden = !visible; };

    let state = config.state;
    let bundles = Number(config.bundles) || 0;
    let clockOffset = state ? state.server_time - Date.now() : 0;
    let timer = null;
    let busy = false;
    let changed = false;

    const stop = () => { clearInterval(timer); timer = null; stopScript(); };

    // Бегущая строка манускрипта: при каждом старте случайный текст (латынь или иврит),
    // скорость печати подгоняется так, чтобы текст дочитывался ровно к концу изучения (~1 минута).
    // la — латынь, grc — древнегреческий, non — древнеисландский рунами (Старший футарк),
    // cu — русский текст глаголицей (можно расшифровать), sa — санскрит, ar — арабский, he — иврит.
    const manuscripts = [
        {"lang": "la", "rtl": false, "italic": true, "text": "Liber secretorum, a sapientibus antiquis conscriptus. Arcana mundi in tenebris latent, sed oculus sapientis ea videt. Qui legit et intellegit, claves aeternitatis tenet; qui legit et non intellegit, verba tantum audit, sicut ventus inter sepulcra. Primum disce nomina rerum, deinde nomina monstrorum, postremo nomen tuum, quod saepe ignoratur. In profundis tumulis dormiunt bestiae magnae, quarum corda lapidea sunt et quarum oculi in somno quoque vigilant. Cave igitur, lector, ne somnum earum turbes sine signo protectionis. Signum autem est circulus cum serpente caudam suam mordente, quod aeternitatem significat et redeuntem semper mundum. Haec scripsi ego, custos bibliothecae, nocte tertia post lunam novam, cum lucerna mea paene exstincta esset."},
        {"lang": "la", "rtl": false, "italic": true, "text": "De monstro magno quod sub tumulo habitat. Narrant senes vici monstrum ingens in ventre tumuli antiqui dormire, cuius venter terram ipsam devorat et ossa eorum qui accesserunt in lapides convertit. Qui stomachum eius aperuerit, lapidem invenit gravem et levem simul, ossibus intextum, qui viridi luce in tenebris lucet. Hunc lapidem medici quaerunt, alchimistae emunt, stulti autem perdunt. Monstrum non statim pugnat: primum circumit, deinde exspectat, postremo impetum facit cum viator fessus est. Igitur noli umquam solus ad tumulum ire, et cum socio semper pugna. Si fortuna favet, monstrum cadet et lapis tuus erit; si non favet, tu ipse lapis eris in ventre eius, cum ceteris qui ante te venerunt et nomina sua in muris reliquerunt."},
        {"lang": "la", "rtl": false, "italic": true, "text": "De scorpione ossium et veneno eius. Scorpius ossium non natus est sed factus: ex ossibus mortuorum compositus, a necromante antiquo animatus, qui nomen suum in cauda eius inscripsit. Aculeus eius ossibus albis constat, et gutta veneni in extremo semper manet, numquam cadens, numquam siccata. Qui hanc guttam tetigerit, febre ardebit per tres dies et per tres noctes, et nisi antidotum habuerit, quarto die obdormiet in aeternum. Pugnator prudens primum chelas frangit, deinde caudam, ultimo caput, quia caput scorpionis nihil sentit et numquam moritur prius. Aculeum autem caute abscinde et in panno lineo involve, ne venenum manus tuas tangat. Hoc praemium magnum est et pretiosum, sed cave ne cupiditas te caecum faciat."},
        {"lang": "la", "rtl": false, "italic": true, "text": "De custode sanguineo sepulcri et corde eius. In sepulcro regum oblitorum custos sanguineus vigilat, qui per saecula portas defendit et nullum vivum intrare sinit. Cor eius non pulsat sicut cor hominum, sed lente, semel in hora, et cum pulsat totum sepulcrum tremit et lucernae exstinguuntur. Sacerdotes antiqui eum ritu sanguinis ad locum alligaverunt, ut thesauros regum servaret usque ad finem temporum. Si quis cor eius abstulerit, sepulcrum aperietur et silentium aeternum frangetur; sed caveat, nam cor etiam extra corpus vivit et sanguinem novum quaerit. Hoc cor in vase argenteo serva, et numquam per noctem lunae plenae aperias. Haec omnia vidi oculis meis, et nunc manus mea tremit dum scribo."},
        {"lang": "grc", "rtl": false, "italic": true, "text": "Βίβλος τῶν ἀπορρήτων, ἣν οἱ σοφοὶ τῶν ἀρχαίων ἔγραψαν. Τὰ μυστήρια τοῦ κόσμου ἐν σκότει κρύπτεται, ὁ δὲ ὀφθαλμὸς τοῦ σοφοῦ βλέπει αὐτά. Ὁ ἀναγινώσκων καὶ νοῶν τὰς κλεῖς τῆς αἰωνιότητος κατέχει· ὁ δὲ ἀναγινώσκων καὶ μὴ νοῶν μόνον ῥήματα ἀκούει, ὡς ἄνεμος ἐν μνήμασιν. Πρῶτον μάνθανε τὰ ὀνόματα τῶν πραγμάτων, ἔπειτα τὰ ὀνόματα τῶν τεράτων, τελευταῖον δὲ τὸ σὸν ὄνομα, ὃ πολλοὶ ἀγνοοῦσιν. Ἐν τοῖς βάθεσι τῶν τύμβων καθεύδουσι θηρία μεγάλα, ὧν αἱ καρδίαι λίθιναί εἰσιν. Φύλαξαι οὖν, ὦ ἀναγνῶστα, μὴ ταράξῃς τὸν ὕπνον αὐτῶν ἄνευ σημείου φυλακῆς. Τὸ δὲ σημεῖόν ἐστι κύκλος καὶ ὄφις τὴν οὐρὰν ἑαυτοῦ ἐσθίων, ὁ οὐροβόρος, σύμβολον τοῦ αἰῶνος."},
        {"lang": "grc", "rtl": false, "italic": true, "text": "Περὶ τοῦ δράκοντος τοῦ ἐν τοῖς ὄρεσιν. Λέγουσιν οἱ γέροντες ὅτι δράκων μέγας καθεύδει ὑπὸ τὰ ὄρη, καὶ ἡ ἀναπνοὴ αὐτοῦ πῦρ ἐστι καὶ τὰ λέπη αὐτοῦ χαλκῷ ὅμοια. Ὅταν δὲ ἐγείρηται, ἡ γῆ σείεται καὶ οἱ ποταμοὶ ξηραίνονται. Ὁ δὲ θέλων νικῆσαι αὐτὸν οὐ μόνον ξίφους δεῖται ἀλλὰ καὶ σοφίας· πρῶτον γὰρ δεῖ γνῶναι τὸ ὄνομα αὐτοῦ, ὃ γέγραπται ἐν τῇ ἀρχαίᾳ βίβλῳ τῶν φυλάκων. Πολλοὶ ἥρωες ἀνῆλθον πρὸς τὸ σπήλαιον αὐτοῦ, ὀλίγοι δὲ κατῆλθον. Οἱ δὲ ἐπανελθόντες ἤνεγκαν ὀδόντας καὶ λέπη, ἃ νῦν ἐν τοῖς ναοῖς κρέμανται. Μὴ οὖν σπεῦδε, ὦ πολεμιστά, ἀλλὰ πρότερον ἀνάγνωθι πάντα ὅσα γέγραπται, καὶ τότε ἔξελθε μετὰ τῶν ἑταίρων σου."},
        {"lang": "grc", "rtl": false, "italic": true, "text": "Περὶ τῆς χρυσοποιίας καὶ τοῦ λίθου τῶν φιλοσόφων. Ἓν τὸ πᾶν, καὶ δι᾿ αὐτοῦ τὸ πᾶν, καὶ εἰς αὐτὸ τὸ πᾶν. Ὁ μόλυβδος ἐν τῷ πυρὶ καθαίρεται, καὶ ἡ ψυχὴ ἐν τῇ σιγῇ. Λάβε τὸ ὕδωρ τὸ θεῖον καὶ μῖξον μετὰ τῆς τέφρας τοῦ φοίνικος, καὶ ἕψε ἑπτὰ ἡμέρας καὶ ἑπτὰ νύκτας, μηδενὶ λαλῶν. Τότε ὄψει τὸ χρῶμα μεταβαλλόμενον, ἐκ μέλανος εἰς λευκόν, ἐκ λευκοῦ εἰς ξανθόν, ἐκ ξανθοῦ εἰς ἐρυθρόν. Τοῦτο τὸ ἐρυθρόν ἐστιν ὁ λίθος, ὃν πολλοὶ ἐζήτησαν καὶ οὐχ εὗρον. Ἀλλὰ φύλαξαι· ὁ λίθος οὐ δίδοται τῷ ἀπλήστῳ, ἀλλὰ τῷ ὑπομένοντι. Ταῦτα ἔγραψα ἐγώ, μαθητὴς τοῦ μεγάλου διδασκάλου, ἐν τῷ ἐνιαυτῷ τοῦ μεγάλου σεισμοῦ, ὅτε ὁ οὐρανὸς ἐσκοτίσθη."},
        {"lang": "non", "rtl": false, "italic": false, "text": "ᛖᚲ᛫ᚹᛁᛏᚲᛁ᛫ᚱᛖᛁᛊᛏ᛫ᚱᚢᚾᚨᚱ᛫ᚦᛖᛊᛊᚨᚱ᛫ᚨ᛫ᛊᛏᛖᛁᚾ᛫ᛁᚾᚾ᛫ᛗᛁᚲᛚᚨ ᛭ ᚺᛖᚱ᛫ᛚᛁᚷᚷᚱ᛫ᛞᚱᛖᚲᛁ᛫ᚢᚾᛞᛁᚱ᛫ᚺᚨᚢᚷᛁ ᛬ ᛟᚲ᛫ᚷᚢᛚᛚ᛫ᚺᚨᚾᛊ᛫ᛊᛖᚠᚱ᛫ᛗᛖᚦ᛫ᚺᛟᚾᚢᛗ ᛭ ᛊᚨ᛫ᛖᚱ᛫ᚹᛖᚲᚱ᛫ᚺᚨᚾᚾ᛫ᛊᚲᚨᛚ᛫ᛞᛖᛁᛃᚨ᛫ᛁ᛫ᛖᛚᛞᛁ ᛬ ᚾᛖᛗᚨ᛫ᚺᚨᚾᚾ᛫ᚹᛁᛏᛁ᛫ᚾᚨᚠᚾ᛫ᚺᚨᚾᛊ ᛭ ᚹᛖᛁᛏ᛫ᛖᚲ᛫ᚨᛏ᛫ᛖᚲ᛫ᚺᛖᚲᚲ᛫ᚹᛁᚾᛞᚷᚨ᛫ᛗᛖᛁᚦᛁ᛫ᚨ᛫ᚾᛖᛏᚱ᛫ᚨᛚᛚᚨᚱ᛫ᚾᛁᚢ ᛬ ᚷᛖᛁᚱᛁ᛫ᚢᚾᛞᚨᚦᚱ᛫ᛟᚲ᛫ᚷᛖᚠᛁᚾᚾ᛫ᛟᚦᚾᛁ ᛬ ᛊᛃᚨᛚᚠᚱ᛫ᛊᛃᚨᛚᚠᚢᛗ᛫ᛗᛖᚱ ᛬ ᚨ᛫ᚦᛖᛁᛗ᛫ᛗᛖᛁᚦᛁ᛫ᛖᚱ᛫ᛗᚨᚾᛜᛁ᛫ᚹᛖᛁᛏ᛫ᚺᚹᛖᚱᛊ᛫ᚨᚠ᛫ᚱᛟᛏᚢᛗ᛫ᚱᛖᚾᚾ ᛭ ᚹᛁᚦ᛫ᚺᛚᛖᛁᚠᛁ᛫ᛗᛁᚲ᛫ᛊᛖᛚᛞᚢ᛫ᚾᛖ᛫ᚹᛁᚦ᛫ᚺᛟᚱᚾᛁᚷᛁ ᛬ ᚾᛁᛊᛏᚨ᛫ᛖᚲ᛫ᚾᛁᚦᚱ ᛬ ᚾᚨᛗ᛫ᛖᚲ᛫ᚢᛈᛈ᛫ᚱᚢᚾᚨᚱ ᛬ ᛖᛈᚨᚾᛞᛁ᛫ᚾᚨᛗ ᛬ ᚠᛖᛚᛚ᛫ᛖᚲ᛫ᚨᚠᛏᚱ᛫ᚦᚨᚦᚨᚾ ᛭ ᚱᚢᚾᚨᚱ᛫ᛗᚢᚾᛏ᛫ᚦᚢ᛫ᚠᛁᚾᚾᚨ᛫ᛟᚲ᛫ᚱᚨᚦᚾᚨ᛫ᛊᛏᚨᚠᛁ ᛬ ᛗᛃᛟᚲ᛫ᛊᛏᛟᚱᚨ᛫ᛊᛏᚨᚠᛁ ᛬ ᛗᛃᛟᚲ᛫ᛊᛏᛁᚾᚾᚨ᛫ᛊᛏᚨᚠᛁ ᛬ ᛖᚱ᛫ᚠᚨᚦᛁ᛫ᚠᛁᛗᛒᚢᛚᚦᚢᛚᚱ ᛭ ᚹᚨᚱᚨᛊᚲ᛫ᚦᚢ᛫ᚹᛖᚷᚠᚨᚱᚨᚾᛞᛁ᛫ᛟᚲ᛫ᚠᚨᚱ᛫ᛖᛁᚷᛁ᛫ᛖᛁᚾᚾ᛫ᛏᛁᛚ᛫ᚺᚨᚢᚷᛊᛁᚾᛊ ᛭"},
        {"lang": "non", "rtl": false, "italic": false, "text": "ᚺᚨᚢᚷᛒᚢᛁᚾᚾ᛫ᚹᚨᚲᛁᚱ᛫ᛁ᛫ᛗᛁᚱᚲᚱᛁ ᛬ ᛟᚲ᛫ᚨᚢᚷᚢ᛫ᚺᚨᚾᛊ᛫ᛒᚱᛖᚾᚾᚨ᛫ᛊᛖᛗ᛫ᚷᛚᛟᚦᛁᚱ ᛭ ᚺᚨᚾᚾ᛫ᚷᛖᛏᛁᚱ᛫ᚷᚢᛚᛚᛊ᛫ᛟᚲ᛫ᚷᚱᛁᛈᚨ᛫ᚲᛟᚾᚢᛜᚨ᛫ᚦᛖᛁᚱᚨ᛫ᛖᚱ᛫ᚠᛁᚱᛁᚱ᛫ᛚᛟᛜᚢ᛫ᛞᛟᚢ ᛭ ᛗᚨᚱᚷᛁᚱ᛫ᚲᚨᛈᛈᚨᚱ᛫ᚠᛟᚱᚢ᛫ᛏᛁᛚ᛫ᚺᚨᚢᚷᛊᛁᚾᛊ ᛬ ᛖᚾ᛫ᚠᚨᛁᚱ᛫ᚲᛟᛗᚢ᛫ᚨᛈᛏᚱ ᛭ ᚦᛖᛁᚱ᛫ᛖᚱ᛫ᚨᛈᛏᚱ᛫ᚲᛟᛗᚢ᛫ᛒᚨᚱᚢ᛫ᛒᛖᛁᚾ᛫ᛟᚲ᛫ᛏᛖᚾᚾᚱ ᛬ ᛟᚲ᛫ᚾᚢ᛫ᚺᚨᛜᚨ᛫ᚦᛖᚱ᛫ᛁ᛫ᚺᛟᚠᛁᚾᚢ ᛭ ᛖᚠ᛫ᚦᚢ᛫ᚹᛁᛚᛚ᛫ᛊᛁᚷᚱᚨ᛫ᚺᚨᚢᚷᛒᚢᚨᚾᚾ ᛬ ᛏᚨᚲ᛫ᛗᛖᚦ᛫ᚦᛖᚱ᛫ᛊᚨᛚᛏ ᛬ ᛃᚨᚱᚾ᛫ᛟᚲ᛫ᛖᛚᛞ ᛬ ᛟᚲ᛫ᛚᛁᛏ᛫ᛖᛁᚷᛁ᛫ᛁ᛫ᚨᚢᚷᚢ᛫ᚺᚨᚾᛊ ᛭ ᚺᚨᚾᚾ᛫ᛏᚨᛚᚨᚱ᛫ᚠᚨᚷᚱᛏ ᛬ ᛖᚾ᛫ᛟᚱᚦ᛫ᚺᚨᚾᛊ᛫ᛖᚱᚢ᛫ᛚᛁᚷᛁ ᛭ ᛞᛖᛁᚱ᛫ᚠᛖ ᛬ ᛞᛖᛁᛃᚨ᛫ᚠᚱᛖᚾᛞᚱ ᛬ ᛞᛖᛁᚱ᛫ᛊᛃᚨᛚᚠᚱ᛫ᛁᛏ᛫ᛊᚨᛗᚨ ᛬ ᛖᚾ᛫ᛟᚱᚦᛊᛏᛁᚱᚱ᛫ᛞᛖᛁᚱ᛫ᚨᛚᛞᚱᛖᚷᛁ᛫ᚺᚹᛖᛁᛗ᛫ᛖᚱ᛫ᛊᛖᚱ᛫ᚷᛟᚦᚨᚾ᛫ᚷᛖᛏᚱ ᛭ ᚦᚹᛁ᛫ᛊᚲᚨᛚᛏᚢ᛫ᛒᛖᚱᛃᚨᛊᚲ᛫ᛗᛖᚦ᛫ᛊᛖᛗᛞ ᛬ ᛟᚲ᛫ᚦᚨ᛫ᛗᚢᚾ᛫ᚾᚨᚠᚾ᛫ᚦᛁᛏᛏ᛫ᛚᛁᚠᚨ᛫ᛚᛖᛜᛁ᛫ᛖᛈᛏᛁᚱ᛫ᚦᛁᚲ ᛭"},
        {"lang": "non", "rtl": false, "italic": false, "text": "ᚠᛃᛟᚱᚨᚱ᛫ᚱᚢᚾᚨᚱ᛫ᚱᛁᛊᛏᚢ᛫ᛞᚹᛖᚱᚷᚨᚱ᛫ᛁ᛫ᛒᛖᚱᚷᛁ᛫ᛞᛃᚢᛈᚢ ᛭ ᚠᛖ᛫ᚹᛖᛚᛞᚱ᛫ᚠᚱᛖᚾᛞᚨ᛫ᚱᛟᚷᛁ ᛬ ᚢᚱᚱ᛫ᛖᚱ᛫ᚨᚠ᛫ᛁᛚᛚᚢ᛫ᛃᚨᚱᚾᛁ ᛬ ᚦᚢᚱᛊ᛫ᚹᛖᛚᛞᚱ᛫ᚲᚹᛖᚾᚾᚨ᛫ᚲᚹᛁᛚᛚᚢ ᛬ ᛟᛊᛊ᛫ᛖᚱ᛫ᚠᛚᛖᛊᛏᚱᚨ᛫ᚠᛖᚱᚦᚨ᛫ᚠᛟᚱ ᛭ ᛊᚨ᛫ᛖᚱ᛫ᚲᚨᚾᚾ᛫ᚱᚢᚾᚨᚱ᛫ᚲᚨᚾᚾ᛫ᛟᚲ᛫ᚷᚨᛚᛞᚱᚨ ᛬ ᛟᚲ᛫ᛊᚨ᛫ᛖᚱ᛫ᚲᚨᚾᚾ᛫ᚷᚨᛚᛞᚱᚨ᛫ᚲᚨᚾᚾ᛫ᚨᛏ᛫ᚹᛖᚲᛃᚨ᛫ᛞᚨᚢᚦᚨ ᛭ ᛖᚾ᛫ᚹᚨᚱᚨᛊᚲ ᛬ ᚦᚹᛁ᛫ᚨᛏ᛫ᛞᚨᚢᚦᛁᚱ᛫ᛗᚢᚾᚨ ᛬ ᛟᚲ᛫ᚦᛖᛁᚱ᛫ᛖᚱ᛫ᚹᚨᚲᛏᛁᚱ᛫ᛖᚱᚢ᛫ᚲᚱᛖᚠᛃᚨ᛫ᛒᛚᛟᚦᛊ ᛭ ᛁ᛫ᚾᛟᚱᚦᚱᛁ᛫ᛊᛏᛖᚾᛞᚱ᛫ᛒᛃᚨᚱᚷ᛫ᛖᛁᛏᛏ᛫ᛗᛁᚲᛁᛏ ᛬ ᛟᚲ᛫ᚦᚨᚱ᛫ᚢᚾᛞᛁᚱ᛫ᛊᛖᚠᚱ᛫ᛟᚱᛗᚱ᛫ᛁᚾᚾ᛫ᛗᛁᚲᛚᛁ ᛬ ᛖᚱ᛫ᚷᚾᚨᚷᚨᚱ᛫ᚱᛖᛏᚱ᛫ᚺᛖᛁᛗᛊᛏᚱᛖᛊᛁᚾᛊ ᛭ ᚦᚨ᛫ᛖᚱ᛫ᚺᚨᚾᚾ᛫ᚹᚨᚲᚾᚨᚱ ᛬ ᛗᚢᚾ᛫ᚺᛖᛁᛗᚱᛁᚾᚾ᛫ᛒᚱᛖᚾᚾᚨ ᛬ ᛟᚲ᛫ᛊᛟᛚᛁᚾ᛫ᛊᛟᚱᛏᚾᚨ ᛬ ᛟᚲ᛫ᛊᛏᛃᛟᚱᚾᚢᚱ᛫ᚺᚹᛖᚱᚠᚨ᛫ᚨᚠ᛫ᚺᛁᛗᚾᛁ ᛭ ᚱᛁᛊᛏ᛫ᚦᚹᛁ᛫ᚱᚢᚾᚨᚱ᛫ᚹᚨᚱᛚᛁᚷᚨ ᛬ ᚹᛖᚷᚠᚨᚱᚨᚾᛞᛁ ᛭"},
        {"lang": "cu", "rtl": false, "italic": false, "text": "ⰵⱅⱁ ⰽⱀⰹⰳⰰ ⱅⰰⰹⱀ, ⰽⱁⱅⱁⱃⱆⱓ ⱀⰰⱂⰹⱄⰰⰾⰹ ⰴⱃⰵⰲⱀⰹⰵ ⰿⱆⰴⱃⰵⱌⱏⰹ. ⱅⰰⰹⱀⱏⰹ ⰿⰹⱃⰰ ⱄⰽⱃⱏⰹⱅⱏⰹ ⰲⱁ ⱅⱐⰿⰵ, ⱀⱁ ⰳⰾⰰⰸ ⰿⱆⰴⱃⰵⱌⰰ ⰲⰹⰴⰹⱅ ⰹⱈ. ⰽⱅⱁ ⱍⰹⱅⰰⰵⱅ ⰹ ⱂⱁⱀⰹⰿⰰⰵⱅ, ⱅⱁⱅ ⰴⰵⱃⰶⰹⱅ ⰽⰾⱓⱍⰹ ⰲⰵⱍⱀⱁⱄⱅⰹ; ⰰ ⰽⱅⱁ ⱍⰹⱅⰰⰵⱅ ⰹ ⱀⰵ ⱂⱁⱀⰹⰿⰰⰵⱅ, ⱅⱁⱅ ⱄⰾⱏⰹⱎⰹⱅ ⱅⱁⰾⱐⰽⱁ ⱄⰾⱁⰲⰰ, ⰽⰰⰽ ⰲⰵⱅⰵⱃ ⱄⱃⰵⰴⰹ ⰿⱁⰳⰹⰾ. ⱄⱀⰰⱍⰰⰾⰰ ⰲⱏⰹⱆⱍⰹ ⰹⰿⰵⱀⰰ ⰲⰵⱋⰵⰹ, ⱂⱁⱅⱁⰿ ⰹⰿⰵⱀⰰ ⱍⱆⰴⱁⰲⰹⱋ, ⰰ ⰲ ⰽⱁⱀⱌⰵ ⱄⰲⱁⰵ ⱄⱁⰱⱄⱅⰲⰵⱀⱀⱁⰵ ⰹⰿⱑ, ⰽⱁⱅⱁⱃⱁⰳⱁ ⰿⱀⱁⰳⰹⰵ ⱀⰵ ⰸⱀⰰⱓⱅ. ⰲ ⰳⰾⱆⰱⰹⱀⰵ ⰽⱆⱃⰳⰰⱀⱁⰲ ⱄⱂⱑⱅ ⰲⰵⰾⰹⰽⰹⰵ ⰸⰲⰵⱃⰹ, ⱍⱐⰹ ⱄⰵⱃⰴⱌⰰ ⰹⰸ ⰽⰰⰿⱀⱑ, ⰰ ⰳⰾⰰⰸⰰ ⱀⰵ ⱄⱂⱑⱅ ⰴⰰⰶⰵ ⰲⱁ ⱄⱀⰵ. ⰱⰵⱃⰵⰳⰹⱄⱐ ⰶⰵ, ⱍⰹⱅⰰⱅⰵⰾⱐ, ⱀⰵ ⱅⱃⰵⰲⱁⰶⱐ ⰹⱈ ⱄⱀⰰ ⰱⰵⰸ ⱁⱈⱃⰰⱀⱀⱁⰳⱁ ⰸⱀⰰⰽⰰ. ⰸⱀⰰⰽ ⰶⰵ ⰵⱅⱁⱅ ⰵⱄⱅⱐ ⰽⱃⱆⰳ ⰹ ⰸⰿⰵⰹ, ⰽⱆⱄⰰⱓⱋⰹⰹ ⱄⰲⱁⰹ ⱈⰲⱁⱄⱅ, ⱁⰱⱃⰰⰸ ⰲⰵⱍⱀⱁⱄⱅⰹ ⰹ ⰿⰹⱃⰰ, ⱍⱅⱁ ⰲⱄⰵⰳⰴⰰ ⰲⱁⰸⰲⱃⰰⱋⰰⰵⱅⱄⱑ ⰽ ⱄⰵⰱⰵ."},
        {"lang": "cu", "rtl": false, "italic": false, "text": "ⱄⰽⰰⰸⰰⱀⰹⰵ ⱁ ⰲⰵⰾⰹⰽⱁⰿ ⰸⰿⰵⰵ, ⱍⱅⱁ ⱄⱂⰹⱅ ⱂⱁⰴ ⰳⱁⱃⰰⰿⰹ. ⰳⱁⰲⱁⱃⱑⱅ ⱄⱅⰰⱃⱌⱏⰹ, ⱍⱅⱁ ⰲⰵⰾⰹⰽⰹⰹ ⰸⰿⰵⰹ ⱂⱁⰽⱁⰹⱅⱄⱑ ⱂⱁⰴ ⰳⱁⱃⰰⰿⰹ, ⰴⱏⰹⱈⰰⱀⰹⰵ ⰵⰳⱁ ⰵⱄⱅⱐ ⱁⰳⱁⱀⱐ, ⰰ ⱍⰵⱎⱆⱑ ⰵⰳⱁ ⱂⱁⰴⱁⰱⱀⰰ ⰿⰵⰴⰹ. ⰽⱁⰳⰴⰰ ⰶⰵ ⱁⱀ ⱂⱃⱁⱄⱀⰵⱅⱄⱑ, ⰸⰵⰿⰾⱑ ⰸⰰⰴⱃⱁⰶⰹⱅ ⰹ ⱃⰵⰽⰹ ⰲⱏⰹⱄⱁⱈⱀⱆⱅ. ⰽⱅⱁ ⱈⱁⱍⰵⱅ ⱂⱁⰱⰵⰴⰹⱅⱐ ⰵⰳⱁ, ⱅⱁⰿⱆ ⱀⱆⰶⰵⱀ ⱀⰵ ⱅⱁⰾⱐⰽⱁ ⰿⰵⱍ, ⱀⱁ ⰹ ⰿⱆⰴⱃⱁⱄⱅⱐ: ⱄⱂⰵⱃⰲⰰ ⱀⱆⰶⱀⱁ ⱆⰸⱀⰰⱅⱐ ⰵⰳⱁ ⰹⰿⱑ, ⱍⱅⱁ ⰸⰰⱂⰹⱄⰰⱀⱁ ⰲ ⰴⱃⰵⰲⱀⰵⰹ ⰽⱀⰹⰳⰵ ⱄⱅⱃⰰⰶⰵⰹ. ⰿⱀⱁⰳⰹⰵ ⰲⰹⱅⱑⰸⰹ ⱂⱁⰴⱀⰹⰿⰰⰾⰹⱄⱐ ⰽ ⰵⰳⱁ ⱂⰵⱋⰵⱃⰵ, ⱀⱁ ⰿⰰⰾⱁ ⰽⱅⱁ ⱄⱂⱆⱄⱅⰹⰾⱄⱑ ⱁⰱⱃⰰⱅⱀⱁ. ⰲⰵⱃⱀⱆⰲⱎⰹⰵⱄⱑ ⱂⱃⰹⱀⰵⱄⰾⰹ ⰸⱆⰱⱏⰹ ⰹ ⱍⰵⱎⱆⱓ, ⱍⱅⱁ ⱀⱏⰹⱀⰵ ⰲⰹⱄⱑⱅ ⰲ ⱈⱃⰰⰿⰰⱈ. ⱀⰵ ⱄⱂⰵⱎⰹ ⰶⰵ, ⰲⱁⰹⱀ, ⰰ ⱄⱂⰵⱃⰲⰰ ⱂⱃⱁⱍⱅⰹ ⰲⱄⰵ ⱀⰰⱂⰹⱄⰰⱀⱀⱁⰵ, ⰹ ⱅⱁⰳⰴⰰ ⰲⱏⰹⱈⱁⰴⰹ ⰲⰿⰵⱄⱅⰵ ⱄⱁ ⱄⰲⱁⰹⰿⰹ ⰴⱃⱆⰸⱐⱑⰿⰹ."},
        {"lang": "cu", "rtl": false, "italic": false, "text": "ⱂⱁⰲⰵⱄⱅⱐ ⱁ ⰽⱁⱄⱅⱑⱀⱁⰿ ⱄⰽⱁⱃⱂⰹⱁⱀⰵ ⰹ ⰵⰳⱁ ⱑⰴⰵ. ⰵⱅⱁⱅ ⱄⰽⱁⱃⱂⰹⱁⱀ ⱀⰵ ⱃⱁⰴⰹⰾⱄⱑ, ⰰ ⰱⱏⰹⰾ ⱄⱁⱅⰲⱁⱃⰵⱀ ⰹⰸ ⰽⱁⱄⱅⰵⰹ ⰿⰵⱃⱅⰲⰵⱌⱁⰲ ⰹ ⱁⰶⰹⰲⰾⰵⱀ ⰴⱃⰵⰲⱀⰹⰿ ⰽⱁⰾⰴⱆⱀⱁⰿ, ⰽⱁⱅⱁⱃⱏⰹⰹ ⱀⰰⱂⰹⱄⰰⰾ ⱄⰲⱁⰵ ⰹⰿⱑ ⱀⰰ ⰵⰳⱁ ⱈⰲⱁⱄⱅⰵ. ⰶⰰⰾⱁ ⰵⰳⱁ ⰹⰸ ⰱⰵⰾⱁⰹ ⰽⱁⱄⱅⰹ, ⰹ ⰽⰰⱂⰾⱑ ⱑⰴⰰ ⱀⰰ ⰵⰳⱁ ⰽⱁⱀⱌⰵ ⰲⱄⰵⰳⰴⰰ ⱁⱄⱅⰰⰵⱅⱄⱑ, ⱀⰵ ⱂⰰⰴⰰⰵⱅ ⰹ ⱀⰵ ⰲⱏⰹⱄⱏⰹⱈⰰⰵⱅ. ⰽⱅⱁ ⰽⱁⱄⱀⰵⱅⱄⱑ ⰵⱅⱁⰹ ⰽⰰⱂⰾⰹ, ⰱⱆⰴⰵⱅ ⰳⱁⱃⰵⱅⱐ ⰲ ⰾⰹⱈⱁⱃⰰⰴⰽⰵ ⱅⱃⰹ ⰴⱀⱑ ⰹ ⱅⱃⰹ ⱀⱁⱍⰹ, ⰹ ⰵⱄⰾⰹ ⱀⰵ ⱀⰰⰹⰴⰵⱅ ⱄⱀⰰⰴⱁⰱⱐⱑ, ⱀⰰ ⱍⰵⱅⰲⰵⱃⱅⱏⰹⰹ ⰴⰵⱀⱐ ⱆⱄⱀⰵⱅ ⰲⰵⱍⱀⱏⰹⰿ ⱄⱀⱁⰿ. ⰿⱆⰴⱃⱏⰹⰹ ⰲⱁⰹⱀ ⱄⱂⰵⱃⰲⰰ ⰾⱁⰿⰰⰵⱅ ⰵⰿⱆ ⰽⰾⰵⱎⱀⰹ, ⱂⱁⱅⱁⰿ ⱈⰲⱁⱄⱅ, ⰰ ⰲ ⰽⱁⱀⱌⰵ ⰳⱁⰾⱁⰲⱆ. ⰶⰰⰾⱁ ⰶⰵ ⱁⱄⱅⱁⱃⱁⰶⱀⱁ ⱁⱅⱃⰵⰶⱐ ⰹ ⱁⰱⰵⱃⱀⰹ ⰾⱐⱀⱑⱀⱏⰹⰿ ⱂⰾⰰⱅⰽⱁⰿ, ⱍⱅⱁⰱⱏⰹ ⱑⰴ ⱀⰵ ⰽⱁⱄⱀⱆⰾⱄⱑ ⱅⰲⱁⰹⱈ ⱃⱆⰽ. ⰵⱅⱁ ⱀⰰⱂⰹⱄⰰⰾ ⱑ, ⰽⱀⰹⰶⱀⰹⰽ, ⱂⱃⰹ ⱄⰲⰵⱍⰵ."},
        {"lang": "sa", "rtl": false, "italic": false, "text": "अथ रहस्यग्रन्थः प्राचीनैः ऋषिभिः लिखितः। जगतः रहस्यानि अन्धकारे निगूढानि सन्ति, किन्तु ज्ञानिनः नेत्रं तानि पश्यति। यः पठति अवगच्छति च सः अनन्तस्य कुञ्चिकाः धारयति। यः पठति न अवगच्छति च सः केवलं शब्दान् शृणोति, यथा वायुः श्मशानेषु। प्रथमं वस्तूनां नामानि पठ, ततः राक्षसानां नामानि, अन्ते च स्वस्य नाम, यत् बहवः न जानन्ति। गिरिगुहासु महान्तः पशवः स्वपन्ति, येषां हृदयानि शिलामयानि सन्ति, नेत्राणि च निद्रायाम् अपि जागृतानि। अतः हे पाठक, रक्षाचिह्नं विना तेषां निद्रां मा भञ्ज। चिह्नं तु चक्रम् अस्ति यस्मिन् सर्पः स्वपुच्छं खादति, अनन्तस्य प्रतीकम्।"},
        {"lang": "sa", "rtl": false, "italic": false, "text": "अग्निमीळे पुरोहितं यज्ञस्य देवमृत्विजम्। अग्निः पूर्वेभिर्ऋषिभिरीड्यो नूतनैरुत। असुराणां राजा पर्वतानाम् अधः शृङ्खलाभिः बद्धः स्वपिति, तस्य शृङ्गे च अङ्गाराः ज्वलन्ति ये जले अपि न शाम्यन्ति। यः तस्य शृङ्गं भनक्ति सः अनन्तम् अग्निं प्राप्नोति, किन्तु असुरः न विस्मरति, स्वप्नेषु च तम् अनुधावति। अतः प्राचीनाः ऋषयः तस्य नाम सप्तसु भाषासु लिखित्वा ग्रन्थान् अदहन्, यथा कोऽपि प्रमादात् तम् न आह्वयेत्। यदि त्वं युद्धाय गच्छसि तर्हि लवणम् अयः दीपं च गृहाण, तस्य नेत्रे मा पश्य, यतः तस्य वचनानि मधुराणि असत्यपूर्णानि च।"},
        {"lang": "sa", "rtl": false, "italic": false, "text": "अथ अस्थिमयस्य वृश्चिकस्य कथा। अयं वृश्चिकः न जातः किन्तु कृतः, मृतानाम् अस्थिभिः रचितः, प्राचीनेन मान्त्रिकेण जीवितः। तस्य पुच्छस्य अग्रे विषबिन्दुः सदा तिष्ठति, न पतति न शुष्यति। यः तं बिन्दुं स्पृशति सः त्रीणि दिनानि तिस्रः रात्रीः च ज्वरेण तप्यते, चतुर्थे च दिने चिरनिद्रां गच्छति यदि औषधं न लभते। बुद्धिमान् योद्धा प्रथमं तस्य नखान् भनक्ति, ततः पुच्छम्, अन्ते च शिरः। पुच्छस्य दंशं सावधानं छित्त्वा क्षौमवस्त्रे वेष्टय, यथा विषं तव हस्तौ न स्पृशेत्। एतत् महत् मूल्यवत् च पारितोषिकम्, किन्तु लोभः त्वाम् अन्धं मा करोतु। एतानि सर्वाणि मया स्वनेत्राभ्यां दृष्टानि।"},
        {"lang": "ar", "rtl": true, "italic": false, "text": "كتاب الأسرار الذي كتبه الحكماء الأولون. إن أسرار العالم مخبوءة في الظلام، ولكن عين الحكيم تراها. من قرأ وفهم فقد ملك مفاتيح الخلود، ومن قرأ ولم يفهم فإنما يسمع كلمات، كالريح بين القبور. تعلّم أولاً أسماء الأشياء، ثم أسماء الوحوش، وفي النهاية اسمك أنت، الذي يجهله كثيرون. في أعماق التلال تنام وحوش عظيمة، قلوبها من حجر، وعيونها ساهرة حتى في نومها. فاحذر أيها القارئ أن توقظها بغير علامة الحماية. والعلامة دائرة فيها ثعبان يأكل ذيله، رمز الأبد والعالم الذي يعود إلى نفسه أبداً. كتبت هذا أنا حارس المكتبة، في الليلة الثالثة بعد الهلال، وقد كاد سراجي أن ينطفئ."},
        {"lang": "ar", "rtl": true, "italic": false, "text": "في صنعة الكيمياء وحجر الحكماء. الكل واحد، وبه الكل، وإليه يعود الكل. يتطهّر الرصاص في النار، وتتطهّر الروح في الصمت. خذ الماء الإلهي وامزجه برماد العنقاء، واطبخه سبعة أيام وسبع ليال، ولا تكلّم أحداً. حينئذٍ ترى اللون يتبدّل، من الأسود إلى الأبيض، ومن الأبيض إلى الأصفر، ومن الأصفر إلى الأحمر. وهذا الأحمر هو الحجر الذي طلبه كثيرون فلم يجدوه. ولكن احذر، فالحجر لا يُعطى للطمّاع بل للصابر. هكذا علّمنا المعلّم الأكبر، وهكذا نقلنا عنه في هذا الكتاب، والله أعلم بالصواب."},
        {"lang": "ar", "rtl": true, "italic": false, "text": "في ذكر الجنّي حارس المقبرة وقلبه الدامي. في مقبرة الملوك المنسيين جنّي أحمر يحرس الأبواب منذ قرون، ولا يدع حياً يدخل. قلبه لا ينبض كقلوب البشر، بل ببطء، مرة في كل ساعة، فإذا نبض ارتجفت المقبرة كلها وانطفأت المصابيح. ربطه الكهنة القدماء بالمكان بطقس الدم، ليحرس كنوز الملوك إلى آخر الزمان. فمن انتزع قلبه انفتحت المقبرة وانكسر الصمت الأبدي، ولكن ليحذر، فالقلب يحيا خارج الجسد ويطلب دماً جديداً. احفظ هذا القلب في إناء من فضة، ولا تفتحه ليلة البدر أبداً. كل هذا رأيته بعينيّ، ويدي ترتجف وأنا أكتب."},
        {"lang": "he", "rtl": true, "italic": false, "text": "ספר הסודות אשר נכתב בידי החכמים הקדמונים. בראשית היה הדבר, והסוד נסתר בין השורות, ורק עין החכם תראה אותו. מי שקורא ומבין מחזיק במפתחות הנצח, ומי שקורא ואינו מבין שומע רק מילים, כרוח העוברת בין הקברים. למד תחילה את שמות הדברים, אחר כך את שמות המפלצות, ובסוף את שמך שלך, שרבים אינם יודעים אותו. במעמקי התלים ישנות חיות גדולות, ולבן אבן, ועיניהן פקוחות גם בשנתן. היזהר אפוא, הקורא, פן תעיר אותן בלי אות שמירה. והאות הוא מעגל ובו נחש האוכל את זנבו, סימן לנצח ולעולם השב אל עצמו תמיד. את כל אלה כתבתי אני, שומר הספרייה, בלילה השלישי אחרי מולד הירח, כשנרי כמעט כבה."},
        {"lang": "he", "rtl": true, "italic": false, "text": "על השדים ועל האש השחורה. סוד השדים כתוב באש שחורה על גבי אש לבנה, ומי שקורא אותו בקול רם מזמין אותם אל ביתו. השד הגדול אשר שמו נאסר בשלשלאות ברזל ישן עמוק תחת ההרים, וקרניו בוערות גם בשנתו. אומרים כי מי שישבור את קרנו ימצא בה גחלים שאינן כבות לעולם, אף לא במים. אך השד אינו שוכח, ומי שלקח ממנו דבר ירדוף אחריו בחלומותיו עד יום מותו. לכן החכמים הקדמונים כתבו את שמו בשבע לשונות ושרפו את הכתבים, כדי שאיש לא יקרא לו בטעות. אם בכל זאת יצאת להילחם בו, קח עמך מלח, ברזל ואור, ואל תביט בעיניו כאשר הוא מדבר אליך, כי דבריו מתוקים ומלאי שקר."},
        {"lang": "he", "rtl": true, "italic": false, "text": "על שומר הקבר האדום ועל לבו. בקבר המלכים הנשכחים שומר אדום עומד על המשמר זה מאות שנים, ואינו מניח לשום חי להיכנס. לבו אינו פועם כלב בני האדם, כי אם לאט, פעם אחת בכל שעה, וכאשר הוא פועם הקבר כולו רועד והנרות כבים. הכוהנים הקדמונים קשרו אותו אל המקום בטקס של דם, כדי שישמור על אוצרות המלכים עד אחרית הימים. מי שיעקור את לבו, הקבר ייפתח והדממה הנצחית תישבר, אך ייזהר, כי הלב חי גם מחוץ לגוף ומבקש דם חדש. שמור את הלב הזה בכלי כסף, ואל תפתח אותו לעולם בליל ירח מלא. את כל זה ראיתי בעיניי, וידי רועדת בעודי כותב."},
    ];
    let scriptTimer = null;
    function stopScript() {
        clearTimeout(scriptTimer);
        scriptTimer = null;
    }
    /** @param {number} durationMs за сколько дочитать текст (оставшееся время изучения) */
    function startScript(durationMs) {
        stopScript();
        const node = find('script');
        node.hidden = false;
        const line = manuscripts[Math.floor(Math.random() * manuscripts.length)];
        node.dir = line.rtl ? 'rtl' : 'ltr';
        node.lang = line.lang;
        node.dataset.italic = line.italic ? '1' : '0';
        const letters = Array.from(line.text);
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            node.textContent = line.text;
            return;
        }
        // После знаков препинания пауза длиннее; базовый шаг считаем с их учётом,
        // чтобы весь текст уложился в оставшееся время. Небольшая «дрожь» — для живого письма.
        const isStop = letter => /[.,;:—]/.test(letter);
        const weight = letters.reduce((sum, letter) => sum + (isStop(letter) ? 2.2 : 1), 0);
        const perLetter = Math.max(20, Math.min(200, (durationMs - 800) / weight));
        let index = 0;
        node.textContent = '';
        const typeLetter = () => {
            node.textContent = letters.slice(0, ++index).join('');
            // Длинная строка уезжает влево (для иврита — вправо), как лента.
            node.scrollLeft = line.rtl ? -node.scrollWidth : node.scrollWidth;
            if (index < letters.length) {
                const pause = isStop(letters[index - 1]) ? perLetter * 2.2 : perLetter * (0.7 + Math.random() * 0.6);
                scriptTimer = setTimeout(typeLetter, pause);
            }
        };
        typeLetter();
    }
    function hideScript() {
        stopScript();
        find('script').hidden = true;
    }

    // Счётчики на странице обновляем сразу после результата, не дожидаясь закрытия окна.
    function updateCounters(data) {
        const bundlesText = String(data.bundles);
        document.querySelectorAll('[data-knowledge-bundles]').forEach(node => { node.textContent = bundlesText; });
        document.querySelectorAll('[data-knowledge-bundle-count]').forEach(node => { node.textContent = '×' + bundlesText; });
        const points = new Intl.NumberFormat('ru-RU').format(Number(data.points) || 0).replace(/[\u00a0\u202f]/g, ' ');
        document.querySelectorAll('[data-knowledge-points]').forEach(node => { node.textContent = points; });
    }
    const message = (text, found = false) => {
        const node = find('message');
        node.textContent = text || '';
        node.classList.toggle('is-found', found);
    };
    const buttons = running => {
        show('cancel-wrap', running);
        show('close-wrap', !running);
        show('again-wrap', !running && bundles > 0);
    };

    async function request(url, body = {}) {
        const send = () => fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf },
            body: JSON.stringify(body),
        });
        let response = await send();
        if (response.status === 419) {
            const token = await fetch(config.refreshTokenUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(r => r.json());
            if (token.token) {
                config.csrf = token.token;
                response = await send();
            }
        }
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const validation = data.errors ? Object.values(data.errors).flat()[0] : null;
            throw new Error(validation || data.message || config.labels.error);
        }
        return data;
    }

    function run(data) {
        stop();
        state = data;
        clockOffset = data.server_time - Date.now();
        buttons(true);
        show('progress', true);
        message(config.labels.running);
        startScript(Math.max(3000, data.completes_at - (Date.now() + clockOffset)));
        const tick = () => {
            const now = Date.now() + clockOffset;
            const percent = Math.min(100, Math.max(0, (now - data.started_at) / Math.max(1, data.completes_at - data.started_at) * 100));
            find('fill').style.width = percent + '%';
            find('percent').textContent = Math.floor(percent) + '%';
            if (now >= data.completes_at + 350) {
                stop();
                complete();
            }
        };
        timer = setInterval(tick, 100);
        tick();
    }

    async function start() {
        if (busy) return;
        busy = true;
        message('');
        show('again-wrap', false);
        try {
            const data = await request(config.startUrl);
            busy = false;
            run(data);
        } catch (error) {
            message(error.message);
            buttons(false);
        } finally {
            busy = false;
        }
    }

    async function complete() {
        if (busy || !state) return;
        busy = true;
        show('cancel-wrap', false);
        try {
            const data = await request(config.completeUrl, { token: state.token });
            changed = true;
            bundles = Number(data.bundles) || 0;
            updateCounters(data);
            message(data.message, Boolean(data.found));
            find('fill').style.width = '100%';
            find('percent').textContent = '100%';
        } catch (error) {
            message(error.message);
        } finally {
            state = null;
            busy = false;
            hideScript();
            buttons(false);
        }
    }

    async function cancel() {
        if (busy || !state) return;
        busy = true;
        stop();
        try {
            const data = await request(config.cancelUrl, { token: state.token });
            message(data.message);
        } catch (error) {
            message(error.message);
        } finally {
            state = null;
            busy = false;
            show('progress', false);
            hideScript();
            buttons(false);
        }
    }

    function open() {
        show('modal', true);
        if (state && state.expires_at > Date.now() + clockOffset) run(state);
        else start();
    }

    // Закрытие окна не прерывает изучение: таймер идёт на сервере, при возврате прогресс продолжится.
    function close() {
        if (busy) return;
        show('modal', false);
        if (changed && timer === null) window.location.reload();
    }

    document.querySelectorAll('[data-study-open]').forEach(button => button.addEventListener('click', open));
    find('again').addEventListener('click', start);
    find('cancel').addEventListener('click', cancel);
    find('close').addEventListener('click', close);
    find('dismiss').addEventListener('click', close);
    find('dismiss').addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            close();
        }
    });
    window.addEventListener('pagehide', stop, { once: true });

    // Изучение уже идёт (страницу перезагрузили) — сразу показываем прогресс.
    if (state) open();
})();
