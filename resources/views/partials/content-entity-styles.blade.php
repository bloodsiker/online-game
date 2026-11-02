        .library-entity-card {
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            width: 290px;
            min-height: 72px;
            margin: 5px 7px 5px 0;
            padding: 5px;
            color: #5e3d29;
            vertical-align: middle;
            border: 1px solid #9f6039;
            background: rgba(241, 207, 159, .58) url('{{ asset('img/bg/info/bg_l.gif') }}') repeat;
            box-shadow: inset 0 0 0 2px rgba(255, 244, 210, .5);
        }
        .library-entity-card:hover { color: #72180e; border-color: #71351e; text-decoration: none; background-color: rgba(235, 188, 126, .72); }
        .library-entity-card__image { box-sizing: border-box; flex: 0 0 60px; width: 60px; height: 60px; object-fit: contain; border: 1px solid #ad754d; background: rgba(68, 35, 19, .16); }
        .library-entity-card__image--empty { display: flex; align-items: center; justify-content: center; color: #9c704f; font: bold 24px Georgia, serif; }
        .library-entity-card__body { display: flex; min-width: 0; padding-left: 8px; flex-direction: column; }
        .library-entity-card__type { color: #997053; font-size: 9px; line-height: 13px; text-transform: uppercase; }
        .library-entity-card__title { overflow: hidden; color: #7e1d0a; font-size: 12px; line-height: 17px; text-overflow: ellipsis; white-space: nowrap; }
        .library-entity-card__details { display: block; margin-top: 2px; color: #6c4e39; font-size: 10px; line-height: 13px; }
        .library-entity-card--npc {
            display: inline-flex;
            align-items: stretch;
            width: 209px;
            min-height: 0;
            flex-direction: column;
            vertical-align: top;
        }
        .library-entity-card--npc .library-entity-card__image {
            flex: 0 0 197px;
            width: 200px;
            height: 200px;
            object-fit: contain;
        }
        .library-entity-card--npc .library-entity-card__body {
            align-items: center;
            padding: 7px 4px 3px;
            text-align: center;
        }
        .library-entity-card--npc .library-entity-card__title {
            max-width: 100%;
            font-size: 13px;
            white-space: normal;
        }
        .library-entity-card--npc .library-entity-card__details { max-width: 100%; }
        .library-entity-line { display: inline-flex; max-width: 100%; margin: 2px 3px 2px 0; color: #7e1d0a; line-height: 20px; align-items: center; vertical-align: middle; }
        .library-entity-line:hover { color: #72180e; }
        .library-entity-line__image { width: 18px; height: 18px; margin-right: 4px; object-fit: contain; border: 1px solid #ad754d; background: rgba(68, 35, 19, .12); }
        .library-entity-line__type { margin-right: 3px; color: #997053; font-size: 10px; }
        .library-entity-line__title { color: #7e1d0a; }
        .library-entity-line__details { overflow: hidden; margin-left: 2px; color: #6c4e39; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
