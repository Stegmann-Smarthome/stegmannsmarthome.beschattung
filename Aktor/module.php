jetzt der richtige code:

<?php

// Klassendefinition
class Aktor extends IPSModule {
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();


        ############################## Zufällige Zahl an Instanznamen anhängen für die bessere Unterscheidung

        
        ############################# Erstellen von zusätzlichen Übergordneten Kategorien /Instanzen


        ############################# Erstellen von neuen Variablenprofilen


        ############################## Registrieren der Eigenschaften aus dem Konfigurationsformular (form.json)
        $this->RegisterPropertyInteger("prop_position", 0);
        $this->RegisterPropertyInteger("prop_lamelle", 0);
        $this->RegisterPropertyInteger("prop_helligkeit", 0);
        $this->RegisterPropertyInteger("prop_temperatur", 0);
        $this->RegisterPropertyInteger("prop_wochenplan", 0);
        $this->RegisterPropertyInteger("prop_lamellen_rueckhub", 0);
        $this->RegisterPropertyInteger("prop_wochenplan_grenzwert_helligkeit_runterfahren", 0);
        $this->RegisterPropertyInteger("prop_wochenplan_grenzwert_helligkeit_hochfahren", 0);
        $this->RegisterPropertyInteger("prop_automatik_grenzwert_temperatur", 0);
        $this->RegisterPropertyInteger("prop_automatik_grenzwert_helligkeit", 0);
        $this->RegisterPropertyBoolean("prop_wochenplan_helligkeit", 0);
        $this->RegisterPropertyInteger("prop_azimut_preset", 0);
        $this->RegisterPropertyInteger("prop_azimut_min", 0);
        $this->RegisterPropertyInteger("prop_azimut_max", 360);
        $this->RegisterPropertyInteger("prop_azimut", 0);
        $this->RegisterPropertyBoolean("prop_automatikmodus_aktivieren", 0);
        $this->RegisterPropertyString("prop_instanzname", "Beschattung");
        $this->RegisterPropertyInteger("prop_sperrzeit", 0);
        $this->RegisterPropertyBoolean("prop_wochenplan_helligkeit_einstellungen", false);
        $this->RegisterPropertyBoolean('prop_lamellen_rueckhub_aktiv', true);

        //Automatikmodus
        $this->RegisterPropertyBoolean("prop_automatikmodus_runterfahren_helligkeit", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_runterfahren_temperatur", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_runterfahren_azimut", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_hochfahren_helligkeit", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_hochfahren_temperatur", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_hochfahren_azimut", false);
        $this->RegisterPropertyBoolean("prop_automatik_grenzwerte_anzeigen", false);


        


        ############################## Erstellen von Attributen
        $this->RegisterAttributeInteger("attr_former_weekly_schedule", 0);
        $this->RegisterAttributeInteger("attr_HeatingPlanID", 0);
        $this->RegisterAttributeInteger("attr_sperre_bis", 0);
        $this->RegisterAttributeBoolean("attr_last_automatik_aktiv", false);
        // Flag: erste Öffnung noch ausstehend
        $this->RegisterAttributeBoolean('PendingOpen', false);
        // Flag: erstes Schließen noch ausstehend
        $this->RegisterAttributeBoolean('PendingClose', false);
        // Flag: nach Endposition Rückhub auslösen
        $this->RegisterAttributeBoolean('PendingLamellen', false);
        // Ziel‑Position merken, um im MessageSink zu prüfen
        $this->RegisterAttributeInteger('attr_shading_target', 0);


        


        ############################## Erstellen von Variablen im Objektbaum + Zuweisung einer Darstellung
        $this->RegisterVariableInteger('select_modus', "Modus", [
            'PRESENTATION'    => VARIABLE_PRESENTATION_ENUMERATION,
            'OPTIONS'         => json_encode([
                [
                    'Value'            => 0,
                    'Caption'          => 'Manuell',
                    'IconActive'       => true,
                    'IconValue'        => 'hand',
                    'Color'            => 52651,
                ],
                [
                    'Value'            => 1,
                    'Caption'          => 'Wochenplan',
                    'IconActive'       => true,
                    'IconValue'        => 'calendar-week',
                    'Color'            => 52651,
                ],
                /*
                [
                    'Value'            => 2,
                    'Caption'          => 'Automatik',
                    'IconActive'       => true,
                    'IconValue'        => 'arrows-rotate',
                    'Color'            => 52651,
                ]
                */
            ]
                )
        ], 8);

        

        $this->RegisterVariableInteger("set_level_shading", "Level: Beschattung", [
            "PRESENTATION" => VARIABLE_PRESENTATION_SLIDER,
            "ICON" => "percent",
            "SUFFIX" => " %",
            'MIN' => 0,
            'MAX' => 100,
            "STEP_SIZE" => 1

        ],11);

        

        $this->RegisterVariableInteger("set_level_closed", "Level: Geschlossen", [
            "PRESENTATION" => VARIABLE_PRESENTATION_SLIDER,
            "ICON" => "percent",
            "SUFFIX" => " %",
            'MIN' => 0,
            'MAX' => 100,
            "STEP_SIZE" => 1
        ],12);
        


        ############################## Aktivieren der Variablenaktion (Standardaktion)
        $this->EnableAction("select_modus");
        $this->EnableAction("set_level_shading");
        $this->EnableAction("set_level_closed");



        ############################## Vorbelegen der Variablen mit Werten
        SetValue($this->GetIDForIdent("set_level_shading"), 50);   // Setzt den Startwert auf 100
        SetValue($this->GetIDForIdent("set_level_closed"), 100);    // Setzt den Startwert auf 50


        ############################## Anlegen und Registrieren von Timern
        $instanzID = $this->InstanceID;
        $this->RegisterTimer("AutoShadingTimer", 0, "IPS_RequestAction($instanzID, 'check_auto_shading', 0);");
        //$this->RegisterTimer("LamellenRueckhub", 0, "IPS_RequestAction($instanzID, 'lamellen_rueckhub', 0);");
        
        

    }



    // Überschreibt die interne IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();


        ############################## Cleanup: Wochenplan-Helligkeits-Check deaktivieren, falls Checkbox deaktiviert ist
        if (!$this->ReadPropertyBoolean('prop_wochenplan_helligkeit')) {
            // ausstehende Öffnung entfernen
            $this->WriteAttributeBoolean('PendingOpen', false);
            // ausstehendes Schließen entfernen
            $this->WriteAttributeBoolean('PendingClose', false);
            // Abos entfernen
            $hid = $this->ReadPropertyInteger('prop_helligkeit');
            if (IPS_VariableExists($hid)) {
                $this->UnregisterMessage($hid, VM_UPDATE);
            }
        }

    
        ############################## Erstellen, Prüfen und löschen von Links im Objektbaum + Zuweisung einer Darstellung
        $this->LinkCreation("Link_ID_Position", "prop_position", "Position","blinds-open",0);
        $this->LinkCreation("Link_ID_Lamelle", "prop_lamelle", "Lamellen","blinds",1);


        ############################## Wochenplan anlegen / aktualisieren, falls sich die Auswahl sich geändert hat
        $actual_weekly_schedule = $this->ReadPropertyInteger("prop_wochenplan");
        $former_weekly_schedule = $this->ReadAttributeInteger("attr_former_weekly_schedule");

            if ($actual_weekly_schedule !== $former_weekly_schedule ) {
                $this->WeeklySchedule_CreateUpdateDelete($actual_weekly_schedule);
                $this->WriteAttributeInteger("attr_former_weekly_schedule", $actual_weekly_schedule);
            }

        ############################## Übernahme der Werte aus der Azimut Vorauswahl
        $preset = $this->ReadPropertyInteger("prop_azimut_preset");

        $azimutMin = null;
        $azimutMax = null;

        switch ($preset) {
            case 1:  $azimutMin = 135; $azimutMax = 225; break; // Süd
            case 2:  $azimutMin = 100; $azimutMax = 160; break; // Süd-Ost
            case 3:  $azimutMin = 200; $azimutMax = 260; break; // Süd-West
            case 4:  $azimutMin =  60; $azimutMax = 120; break; // Ost
            case 5:  $azimutMin = 240; $azimutMax = 300; break; // West
            case 6:  $azimutMin =  330; $azimutMax = 30; break; // Nord
            case 7:  $azimutMin =  45; $azimutMax =  90; break; // Nor-Ost
            case 8:  $azimutMin = 270; $azimutMax = 315; break; // Nord-West
        }

        if (!is_null($azimutMin) && !is_null($azimutMax)) {
            $currentMin = $this->ReadPropertyInteger("prop_azimut_min");
            $currentMax = $this->ReadPropertyInteger("prop_azimut_max");
    
            if ($currentMin !== $azimutMin  || $currentMax !== $azimutMax) {
                IPS_SetProperty($this->InstanceID, "prop_azimut_min", $azimutMin );
                IPS_SetProperty($this->InstanceID, "prop_azimut_max", $azimutMax);
                IPS_ApplyChanges($this->InstanceID);
                return; // Schleife verhindern
            }
        }

        // Registrierung für manuelle Änderungen an Aktorposition oder Lamellen
        $positionID = $this->ReadPropertyInteger("prop_position");
        $lamelleID  = $this->ReadPropertyInteger("prop_lamelle");

        if (IPS_VariableExists($positionID)) {
            $this->RegisterMessage($positionID, VM_UPDATE);
        }
        if (IPS_VariableExists($lamelleID)) {
            $this->RegisterMessage($lamelleID, VM_UPDATE);
        }


        ############################## Darstellung der Modusauswahl abhängig von Automatik-Property
        $automatikAktiv = $this->ReadPropertyBoolean("prop_automatikmodus_aktivieren");
        $lastAutomatik = $this->ReadAttributeBoolean("attr_last_automatik_aktiv");

       // Nur wenn sich der Zustand geändert hat, neu erstellen
       if ($this->GetIDForIdent('select_modus') !== false) {

        // Neue Optionen aufbauen
        $options = [
            [
                'Value' => 0,
                'Caption' => 'Manuell',
                'IconActive' => true,
                'IconValue' => 'hand',
                'Color' => 52651
            ],
            [
                'Value' => 1,
                'Caption' => 'Wochenplan',
                'IconActive' => true,
                'IconValue' => 'calendar-week',
                'Color' => 52651
            ]
        ];

        if ($automatikAktiv) {
            $options[] = [
                'Value' => 2,
                'Caption' => 'Automatik',
                'IconActive' => true,
                'IconValue' => 'arrows-rotate',
                'Color' => 52651
            ];
        }

        // Neue Variable anlegen
        $this->RegisterVariableInteger('select_modus', "Modus", [
            'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
            'OPTIONS' => json_encode($options)
        ], 8);

        // Zustand merken
        $this->WriteAttributeBoolean("attr_last_automatik_aktiv", $automatikAktiv);
        }
        
        # Instanzname aus Property übernehmen, falls abweichend / Instanzname aktualisieren
        $instanzname = $this->ReadPropertyString("prop_instanzname");
        if ($instanzname != "" && IPS_GetName($this->InstanceID) !== $instanzname) {
            IPS_SetName($this->InstanceID, $instanzname);
        }




        ############################## Helligkeitsgrenzwerte-Variablen bedingt erstellen oder löschen
        $enableLightLevelSettings = $this->ReadPropertyBoolean("prop_wochenplan_helligkeit_einstellungen");

        if ($enableLightLevelSettings) {
            $this->RegisterVariableInteger("set_light_level_up", "Wochenplan: Helligkeit hoch", [
                "PRESENTATION" => VARIABLE_PRESENTATION_SLIDER,
                "ICON" => "brightness",
                "SUFFIX" => " Lux",
                'MIN' => 0,
                'MAX' => 1000,
                "STEP_SIZE" => 1
            ], 13);

            $this->RegisterVariableInteger("set_light_level_down", "Wochenplan: Helligkeit runter", [
                "PRESENTATION" => VARIABLE_PRESENTATION_SLIDER,
                "ICON" => "brightness",
                "SUFFIX" => " Lux",
                'MIN' => 0,
                'MAX' => 1000,
                "STEP_SIZE" => 1
            ], 14);

            $this->EnableAction("set_light_level_up");
            $this->EnableAction("set_light_level_down");

            // Werte aus Properties übernehmen (in zwei Schritten für eine minimale Verzögerung)
            $upID = $this->GetIDForIdent('set_light_level_up');
            $downID = $this->GetIDForIdent('set_light_level_down');
            
            SetValue($upID, $this->ReadPropertyInteger('prop_wochenplan_grenzwert_helligkeit_hochfahren'));
            SetValue($downID, $this->ReadPropertyInteger('prop_wochenplan_grenzwert_helligkeit_runterfahren'));
        } else {
            if (@$this->GetIDForIdent("set_light_level_up")) {
                $this->UnregisterVariable("set_light_level_up");
            }
            if (@$this->GetIDForIdent("set_light_level_down")) {
                $this->UnregisterVariable("set_light_level_down");
            }

        
        // --- Alten WeeklySchedule-Timer (falls vorhanden) löschen ---
        $timerIdent = 'WeeklyScheduleTimer';
        $timerID = @IPS_GetObjectIDByIdent($timerIdent, $this->InstanceID);
        if ($timerID !== false && IPS_EventExists($timerID)) {
            IPS_DeleteEvent($timerID);
            IPS_LogMessage('Beschattung', "WeeklyScheduleTimer (ID $timerID) gelöscht.");
        }


        // Not released
        // Anwenden um den aktuellen eingsetellten Modus zur prüfen bzw. neu zu setzen
        //$current = GetValue($this->GetIDForIdent('select_modus'));
        //$this->Shutter_ModusSelect($current);


        ######### Abwärtskompatibiltät - nicht löschen - stellt sicher Attribute angelegt werden, die erst dazukammen nachdem die Instanz schon erstellt war
        if (@$this->ReadAttributeBoolean('PendingOpen') === null) {
            $this->RegisterAttributeBoolean('PendingOpen', false);
        }
        if (@$this->ReadAttributeBoolean('PendingClose') === null) {
            $this->RegisterAttributeBoolean('PendingClose', false);
        }

        $timerID = @IPS_GetObjectIDByIdent('LamellenRueckhub', $this->InstanceID);
        if ($timerID !== false && IPS_EventExists($timerID)) {
            IPS_DeleteEvent($timerID);
            IPS_LogMessage('Beschattung', "Alten LamellenRueckhub‑Timer (ID $timerID) gelöscht.");
        }


        // Backwards‑Compatibility: fehlende Attribute nachregistrieren
        if (@$this->ReadAttributeBoolean('PendingLamellen') === null) {
            $this->RegisterAttributeBoolean('PendingLamellen', false);
        }
        if (@$this->ReadAttributeInteger('attr_shading_target') === null) {
            $this->RegisterAttributeInteger('attr_shading_target', 0);
        }


        

    }




    $showAutoThresholds = $this->ReadPropertyBoolean("prop_automatik_grenzwerte_anzeigen");

    if ($showAutoThresholds) {
        $this->RegisterVariableInteger("set_auto_temp_threshold", "Automatik: Temperatur", [
            "PRESENTATION" => VARIABLE_PRESENTATION_SLIDER,
            "ICON" => "arrows-rotate",
            "SUFFIX" => " °C",
            'MIN' => 0,
            'MAX' => 35,
            "STEP_SIZE" => 1
        ], 15);
    
        $this->RegisterVariableInteger("set_auto_light_threshold", "Automatik: Helligkeit", [
            "PRESENTATION" => VARIABLE_PRESENTATION_SLIDER,
            "ICON" => "arrows-rotate",
            "SUFFIX" => " Lux",
            'MIN' => 0,
            'MAX' => 10000,
            "STEP_SIZE" => 100
        ], 16);
    
        $this->EnableAction("set_auto_temp_threshold");
        $this->EnableAction("set_auto_light_threshold");
    
        SetValue($this->GetIDForIdent("set_auto_temp_threshold"), $this->ReadPropertyInteger('prop_automatik_grenzwert_temperatur'));
        SetValue($this->GetIDForIdent("set_auto_light_threshold"), $this->ReadPropertyInteger('prop_automatik_grenzwert_helligkeit'));
    } else {
        if (@$this->GetIDForIdent("set_auto_temp_threshold")) {
            $this->UnregisterVariable("set_auto_temp_threshold");
        }
        if (@$this->GetIDForIdent("set_auto_light_threshold")) {
            $this->UnregisterVariable("set_auto_light_threshold");
        }
    }
    

    
    }





    public function LinkCreation($ident,$property,$name,$icon,$position)
    {
        // Ermittlung der ID eines vorhandenen Links
        $linkID = @$this->GetIDForIdent($ident);
        // Abruf der hinterlegteen ID aus dem Konfigurationsformular
        $targetID = $this->ReadPropertyInteger($property);

        // Wenn ein Link existiert, aber keine gültige Ziel-ID mehr ausgewählt ist, dann löschen
        if ($linkID !== false 
            && (!IPS_VariableExists($targetID) || $targetID === 0)
        ) {
            IPS_DeleteLink($linkID);                                // Link-Objekt entfernen
            $linkID = false;                                        // Markiere, dass kein Link mehr existiert
        }
    
        // Wenn kein Link existiert und jetzt eine gültige Ziel-Variable ausgewählt ist, dann neu anlegen
        if ($linkID === false && IPS_VariableExists($targetID) && $targetID > 0
        ) {
            $ist_temperatur = IPS_CreateLink();                     // Link-Objekt erstellen
            IPS_SetName($ist_temperatur, $name);                    // Link benennen
            IPS_SetParent($ist_temperatur, $this->InstanceID);      // Unter diesem Modul ablegen
            IPS_SetLinkTargetID($ist_temperatur, $targetID);        // Ziel-Variable setzen
            IPS_SetIdent($ist_temperatur, $ident);                  // Ident vergeben
            $linkID = $ist_temperatur;                              // neue Link-ID merken
            IPS_SetIcon($linkID, $icon);                            // Icon zuweisen
            IPS_SetPosition($linkID, $position);                    // Position festlegen
        }

        // Wenn Link und Ziel existieren, Ziel aktualisieren
        if ($linkID !== false && IPS_VariableExists($targetID) && $targetID > 0) {
            IPS_SetLinkTargetID($linkID, $targetID);
        }
    }

    private function WeeklySchedule_CreateUpdateDelete($selection)
    {
        $actorID    = $this->ReadPropertyInteger("prop_position");
        $open       = 0;
        $closed     = $this->GetIDForIdent("set_level_closed");

        // 1. Alten Plan löschen, falls vorhanden
        $existingPlanID = $this->ReadAttributeInteger("attr_HeatingPlanID");
        if ($existingPlanID > 0 && IPS_EventExists($existingPlanID)) {
            IPS_DeleteEvent($existingPlanID);
            $this->WriteAttributeInteger("attr_HeatingPlanID", 0);
            IPS_LogMessage("Beschattung", "Alter Wochenplan (ID {$existingPlanID}) gelöscht.");
        }

        // 2. Wenn Wert = 0, dann gar keinen neuen Plan anlegen
        if ($selection === 0) {
            return;
        }

        // 3. Neues Schedule-Event anlegen (Typ 2 = Zeitplan)
        $heatingPlan = IPS_CreateEvent(2);
        $this->WriteAttributeInteger("attr_HeatingPlanID", $heatingPlan);
        IPS_SetParent($heatingPlan, $this->InstanceID);
        IPS_SetIdent($heatingPlan, "HeatingPlan");
        IPS_SetName($heatingPlan, "Wochenplan");
        IPS_SetEventActive($heatingPlan, true);
        IPS_SetPosition($heatingPlan, 9);
        IPS_SetIcon($heatingPlan, "calendar-clock");

        // 4. Wochenplan Aktionen anlegen
        //ALT: IPS_SetEventScheduleAction($heatingPlan, 0, "Offen", 0x00FF00, "RequestAction({$actorID}, GetValue({$open}));");
        //ALT: IPS_SetEventScheduleAction($heatingPlan, 1, "Geschlossen", 0xFF0000, "RequestAction({$actorID}, GetValue({$closed}));");

        //IPS_SetEventScheduleAction($heatingPlan, 0, "Offen", 0x00FF00, "BS_Beschattung_Wochenplan(\$_IPS['TARGET'], \$_IPS['ACTION']);");
        //IPS_SetEventScheduleAction($heatingPlan, 1, "Geschlossen", 0xFF0000, "BS_Beschattung_Wochenplan(\$_IPS['TARGET'], \$_IPS['ACTION']);");

        // WeeklySchedule_CreateUpdateDelete:
        $instanzID = $this->InstanceID;
        IPS_SetEventScheduleAction($heatingPlan, 0, "Offen", 0x00FF00, "IPS_RequestAction($instanzID, 'weekly_schedule', 0);");
        IPS_SetEventScheduleAction($heatingPlan, 1, "Geschlossen", 0xFF0000, "IPS_RequestAction($instanzID, 'weekly_schedule', 1);");






        // 5. Jetzt erst die Gruppen definieren (je nach Auswahl Value):
        switch ($selection) {
            case 1:
                // Beispiel: nur Gruppe 0 für alle Wochentage
                IPS_SetEventScheduleGroup($heatingPlan, 0, 127);
                IPS_SetEventScheduleGroupPoint($heatingPlan, 0, 0,  0,  0, 0, 1);   // 00:00 Uhr
                IPS_SetEventScheduleGroupPoint($heatingPlan, 0, 1,  6,  0, 0, 0);   // 06:00 Uhr → Offen
                IPS_SetEventScheduleGroupPoint($heatingPlan, 0, 2, 20,  0, 0, 1);   // 20:00 Uhr → Geschlossen
                break;

            case 2:
                // Beispiel: Gruppe 0 = Mo–Fr, Gruppe 1 = Sa–So
                IPS_SetEventScheduleGroup($heatingPlan, 0, 31);  // Bitmaske Mo–Fr
                IPS_SetEventScheduleGroup($heatingPlan, 1, 96);  // Bitmaske Sa–So

                // Für Gruppe 0 (Mo–Fr) drei Punkte
                IPS_SetEventScheduleGroupPoint($heatingPlan, 0, 0,  0,  0, 0, 1);
                IPS_SetEventScheduleGroupPoint($heatingPlan, 0, 1,  6,  0, 0, 0);
                IPS_SetEventScheduleGroupPoint($heatingPlan, 0, 2, 20,  0, 0, 1);

                // Für Gruppe 1 (Sa–So) drei Punkte
                IPS_SetEventScheduleGroupPoint($heatingPlan, 1, 0,  0,  0, 0, 1);
                IPS_SetEventScheduleGroupPoint($heatingPlan, 1, 1,  8,  0, 0, 0);
                IPS_SetEventScheduleGroupPoint($heatingPlan, 1, 2, 22,  0, 0, 1);
                break;

            case 3:
                // Beispiel: jede Wochentagsgruppe einzeln (Mo, Di, Mi, … So)
                for ($i = 0; $i < 7; $i++) {
                    IPS_SetEventScheduleGroup($heatingPlan, $i, (1 << $i));
                    IPS_SetEventScheduleGroupPoint($heatingPlan, $i, 0,  0,  0, 0, 0);
                    IPS_SetEventScheduleGroupPoint($heatingPlan, $i, 1,  6,  0, 0, 1);
                    IPS_SetEventScheduleGroupPoint($heatingPlan, $i, 2, 20,  0, 0, 0);
                }
                break;

            default:
                // Falls ganz unerwartet ein anderer Wert kommt, einfach keine Gruppe
                break;
        }

        // 6. Auf Änderungen am Plan (Gruppen/Punkte/Action) lauschen
        $this->RegisterMessage($heatingPlan, EM_UPDATE);
        $this->RegisterMessage($heatingPlan, EM_CHANGESCHEDULEGROUP);
        $this->RegisterMessage($heatingPlan, EM_CHANGESCHEDULEGROUPPOINT);
        $this->RegisterMessage($heatingPlan, EM_CHANGESCHEDULEACTION);

        IPS_LogMessage("Raumregelung", "Neuer Wochenplan angelegt (ID {$heatingPlan}).");
    }


    // Prüft ob die erste oder letzte Aktion des Wochenplans aktiv ist
/**
 * Prüft, ob die aktuelle Aktion die erste Öffnung (ActionID=0)
 * oder die letzte Schließung (ActionID=1) des heutigen Tages ist.
 */
private function IsFirstOrLastScheduleActionToday(int $planID, int $currentActionID): bool
{
    $now   = time();
    $today = (int) date('N', $now); // Mo=1 … So=7
    $event = IPS_GetEvent($planID);

    $openList  = [];
    $closeList = [];

    // 1) über alle Gruppen iterieren
    foreach ($event['ScheduleGroups'] as $group) {
        // gilt diese Gruppe an Wochentag $today?
        if (! ($group['Days'] & (1 << ($today - 1)))) {
            continue;
        }
        // Punkte existieren?
        if (empty($group['Points']) || !is_array($group['Points'])) {
            continue;
        }
        // 2) jede Aktion (Punkt) einsammeln
        foreach ($group['Points'] as $pt) {
            $h  = $pt['Start']['Hour'];
            $m  = $pt['Start']['Minute'];
            $ts = mktime($h, $m, 0);
            if ($pt['ActionID'] === 0) {
                $openList[]  = ['h'=>$h,'m'=>$m,'ts'=>$ts];
            }
            elseif ($pt['ActionID'] === 1) {
                $closeList[] = ['h'=>$h,'m'=>$m,'ts'=>$ts];
            }
        }
    }

    // 3) fehlende Open- oder Close-Punkte?
    if (empty($openList) || empty($closeList)) {
        return false;
    }

    // 4) sortieren: erste Öffnung & letztes Schließen finden
    usort($openList,  fn($a,$b) => $a['ts']  <=> $b['ts']);
    usort($closeList, fn($a,$b) => $a['ts']  <=> $b['ts']);
    $firstOpen = $openList[0];
    $lastClose = end($closeList);

    // 5) aktuelle Stunde/Minute
    $curH = (int) date('G', $now);  // ohne führende Null
    $curM = (int) date('i', $now);

    // 6) je nach ActionID vergleichen
    if ($currentActionID === 0) {
        // nur true zurück, wenn wir gerade bei erster Öffnung sind
        return ($curH === $firstOpen['h'] && $curM === $firstOpen['m']);
    }
    if ($currentActionID === 1) {
        // nur true, wenn wir gerade bei letzter Schließung sind
        return ($curH === $lastClose['h'] && $curM === $lastClose['m']);
    }
    return false;
}

    
    




    private function Shutter_ModusSelect(int $modus)
    {
        $planID = $this->ReadAttributeInteger("attr_HeatingPlanID");

        switch ($modus) {
            case 0: // Manuell
                IPS_LogMessage("Beschattung", "Modus = Manuell → keine Automatik aktiv");
                if ($planID > 0 && IPS_EventExists($planID)) {
                    IPS_SetEventActive($planID, false);     // Event abschalten
                    IPS_SetDisabled($planID, true);         // Objekt deaktivieren
                }
                $this->SetTimerInterval("AutoShadingTimer", 0);                                 // Timer deaktivieren
                break;

            case 1: // Wochenplan
                IPS_LogMessage("Beschattung", "Modus = Wochenplan → Zeitsteuerung aktiv");
                if ($planID > 0 && IPS_EventExists($planID)) {
                    IPS_SetEventActive($planID, true);
                    IPS_SetDisabled($planID, false);
                }
                $this->SetTimerInterval("AutoShadingTimer", 0);                                 // Timer deaktivieren
                break;

            case 2: // Automatik
                IPS_LogMessage("Beschattung", "Modus = Automatik → Sensorsteuerung aktiv");
                if ($planID > 0 && IPS_EventExists($planID)) {
                    IPS_SetEventActive($planID, true);
                    IPS_SetDisabled($planID, false);
                }
                $this->SetTimerInterval("AutoShadingTimer", 5 * 60 * 1000);                     // Timer aktivieren, Invtervall: alle 5 Minuten
                break;

            default:
                IPS_LogMessage("Beschattung", "Unbekannter Modus: $modus");
                break;
        }
    }


    public function CheckAutoShading()
    {
        // Nur ausführen, wenn Modus = Automatik (2)
        $modus = GetValue($this->GetIDForIdent("select_modus"));
        if ($modus !== 2) {
            IPS_LogMessage("Beschattung", "[Automatikprüfung] Abbruch – Modus ist nicht Automatik.");
            return;
        }
    
        // Sperrzeit prüfen
        $now = time();
        $sperreBis = $this->ReadAttributeInteger("attr_sperre_bis");
        if ($sperreBis > $now) {
            $rest = $sperreBis - $now;
            IPS_LogMessage("Beschattung", "[Automatikprüfung] Abbruch – gesperrt für weitere {$rest} Sek.");
            return;
        }
    
        // Wochenplan prüfen
        if (!$this->IsAutomatikErlaubt()) {
            IPS_LogMessage("Beschattung", "[Automatikprüfung] Abbruch – Wochenplan steht nicht auf Aktion 0 (Offen).");
            return;
        }


        // Sensoren & Azimut einlesen
        $helligkeitID = $this->ReadPropertyInteger("prop_helligkeit");
        $temperaturID = $this->ReadPropertyInteger("prop_temperatur");
        $azimutID     = $this->ReadPropertyInteger("prop_azimut");

        $checkLux  = IPS_VariableExists($helligkeitID);
        $checkTemp = IPS_VariableExists($temperaturID);
        $checkAzim = IPS_VariableExists($azimutID);

        $lux    = $checkLux  ? GetValue($helligkeitID)  : null;
        $temp   = $checkTemp ? GetValue($temperaturID)  : null;
        $azimut = $checkAzim ? GetValueFloat($azimutID) : null;

        // Zielwerte
        $positionID = $this->ReadPropertyInteger("prop_position");
        $lamelleID  = $this->ReadPropertyInteger("prop_lamelle");
        $levelBeschattung = GetValue($this->GetIDForIdent("set_level_shading"));
    
    
        // ---------- RUNTERFAHR-BEDINGUNGEN (UND) ----------
    $runterfahrenErlaubt = true;

    if ($this->ReadPropertyBoolean("prop_automatikmodus_runterfahren_helligkeit") && $checkLux) {
        $luxGrenzeRunter = $this->ReadPropertyInteger("prop_automatik_grenzwert_helligkeit");
        if ($lux < $luxGrenzeRunter) {
            IPS_LogMessage("Beschattung", "[Runterfahren] Lux = $lux < $luxGrenzeRunter → OK.");
        } else {
            IPS_LogMessage("Beschattung", "[Runterfahren] Lux = $lux ≥ $luxGrenzeRunter → Abbruch.");
            $runterfahrenErlaubt = false;
        }
    }

    if ($this->ReadPropertyBoolean("prop_automatikmodus_runterfahren_temperatur") && $checkTemp) {
        $tempGrenze = $this->ReadPropertyInteger("prop_automatik_grenzwert_temperatur");
        if ($temp >= $tempGrenze) {
            IPS_LogMessage("Beschattung", "[Runterfahren] Temperatur = $temp ≥ $tempGrenze → OK.");
        } else {
            IPS_LogMessage("Beschattung", "[Runterfahren] Temperatur = $temp < $tempGrenze → Abbruch.");
            $runterfahrenErlaubt = false;
        }
    }

    if ($this->ReadPropertyBoolean("prop_automatikmodus_runterfahren_azimut") && $checkAzim) {
        $azimutMin = $this->ReadPropertyInteger("prop_azimut_min");
        $azimutMax = $this->ReadPropertyInteger("prop_azimut_max");

        $inBereich = ($azimutMin > $azimutMax)
            ? ($azimut >= $azimutMin || $azimut <= $azimutMax)
            : ($azimut >= $azimutMin && $azimut <= $azimutMax);

        if ($inBereich) {
            IPS_LogMessage("Beschattung", "[Runterfahren] Azimut = $azimut innerhalb von $azimutMin–$azimutMax → OK.");
        } else {
            IPS_LogMessage("Beschattung", "[Runterfahren] Azimut = $azimut außerhalb von $azimutMin–$azimutMax → Abbruch.");
            $runterfahrenErlaubt = false;
        }
    }

    // Runterfahren wenn erlaubt
    if ($runterfahrenErlaubt) {
        // 1) Schatten-Position anfahren
        RequestAction($positionID, $levelBeschattung);
        // Rückhub erst nach Ende der Fahrt vorbereiten
        $ruecksatz = $this->ReadPropertyInteger("prop_lamellen_rueckhub");

        if ($this->ReadPropertyBoolean('prop_lamellen_rueckhub_aktiv')) {
            $this->WriteAttributeBoolean('PendingLamellen', true);
            $this->WriteAttributeInteger('attr_shading_target', $levelBeschattung);
            IPS_LogMessage("Beschattung","[Automatik] Rückhub vorbereitet: nach Erreichen von {$levelBeschattung}% → Lamellen auf {$ruecksatz}%.");
        }
        
        
    }



    // -----------------------------------
    // HOCHFAHREN (ODER-Verknüpfung)
    // -----------------------------------
    $hochfahren = false;

    if ($this->ReadPropertyBoolean("prop_automatikmodus_hochfahren_helligkeit") && isset($lux)) {
        $grenze = $this->ReadPropertyInteger("prop_automatik_grenzwert_helligkeit");
        if ($lux < $grenze) {
            $hochfahren = true;
            IPS_LogMessage("Beschattung", "[Hochfahren] Lux < {$grenze} → erlaubt.");
        }
    }

    if ($this->ReadPropertyBoolean("prop_automatikmodus_hochfahren_temperatur") && isset($temp)) {
        $grenze = $this->ReadPropertyInteger("prop_automatik_grenzwert_temperatur");
        if ($temp < $grenze) {
            $hochfahren = true;
            IPS_LogMessage("Beschattung", "[Hochfahren] Temperatur < {$grenze} → erlaubt.");
        }
    }

    if ($this->ReadPropertyBoolean("prop_automatikmodus_hochfahren_azimut") && isset($azimut)) {
        $min = $this->ReadPropertyInteger("prop_azimut_min");
        $max = $this->ReadPropertyInteger("prop_azimut_max");
        $außerhalb = ($min > $max) ? !($azimut >= $min || $azimut <= $max) : !($azimut >= $min && $azimut <= $max);
        if ($außerhalb) {
            $hochfahren = true;
            IPS_LogMessage("Beschattung", "[Hochfahren] Azimut außerhalb → erlaubt.");
        }
    }

    if ($hochfahren) {
        RequestAction($posID, 0);
        IPS_LogMessage("Beschattung", "[Automatik] Hochgefahren.");
        return;
    }



    }
    
    

    public function LamellenRueckhub()
    {
        $this->SetTimerInterval("LamellenRueckhub", 0); // Timer stoppen
        $positionID = $this->ReadPropertyInteger("prop_position");
    
        if (!IPS_VariableExists($positionID)) {
            IPS_LogMessage("Beschattung", "Rueckhub-Ziel konnte nicht ausgeführt werden – Aktor fehlt.");
            return;
        }
    
        $ziel = $this->ReadAttributeInteger("attr_lamellen_soll");
        RequestAction($positionID, $ziel);
        IPS_LogMessage("Beschattung", "Lamellen-Rückhub ausgeführt auf {$ziel}%.");
    }

    // Funktion zum Rücksetzen der Speere
    public function ResetSperre()
    {
        $this->WriteAttributeInteger("attr_sperre_bis", 0);
        IPS_LogMessage("Beschattung", "Automatik-Sperre manuell zurückgesetzt.");
    }
    

    public function Beschattung_Wochenplan(int $id, int $actionID) {
        IPS_LogMessage("Beschattung", "Wochenplan: Aktion $actionID wurde ausgelöst");
    
        $positionID = $this->ReadPropertyInteger("prop_position");
        $planID     = $this->ReadAttributeInteger("attr_HeatingPlanID");
        $zielwert   = ($actionID == 0) ? 0 : 100;
    
        if ($this->ReadPropertyBoolean("prop_wochenplan_helligkeit")
         && $this->IsFirstOrLastScheduleActionToday($planID, $actionID)) {
    
            $lux = GetValue($this->ReadPropertyInteger("prop_helligkeit"));
    
            if ($actionID == 0) {
                // --- Öffnen: blockieren, wenn zu dunkel ---
                $threshold = $this->ReadPropertyInteger("prop_wochenplan_grenzwert_helligkeit_hochfahren");
                if ($lux < $threshold) {
                    IPS_LogMessage("Beschattung", "[Wochenplan] Hochfahren blockiert – Lux={$lux} < {$threshold}");
                    // Nachprüfung aktivieren
                    $this->WriteAttributeBoolean('PendingOpen', true);
                    $hid = $this->ReadPropertyInteger('prop_helligkeit');
                    if (IPS_VariableExists($hid)) {
                        $this->RegisterMessage($hid, VM_UPDATE);
                    }
                    return;
                }
                IPS_LogMessage("Beschattung", "[Wochenplan] Hochfahren erlaubt – Lux={$lux} ≥ {$threshold}");
            }
            else {
                // --- Schließen: blockieren, wenn zu dunkel ---
                $threshold = $this->ReadPropertyInteger("prop_wochenplan_grenzwert_helligkeit_runterfahren");
                if ($lux > $threshold) {
                    IPS_LogMessage("Beschattung", "[Wochenplan] Runterfahren blockiert – Lux={$lux} < {$threshold}");
                    // Nachprüfung aktivieren
                    $this->WriteAttributeBoolean('PendingClose', true);
                    $hid = $this->ReadPropertyInteger('prop_helligkeit');
                    if (IPS_VariableExists($hid)) {
                        $this->RegisterMessage($hid, VM_UPDATE);
                    }
                    return;
                }
                IPS_LogMessage("Beschattung", "[Wochenplan] Runterfahren erlaubt – Lux={$lux} ≥ {$threshold}");
            }
        }
    
        // Ausführung, wenn nicht blockiert oder Helligkeit nicht geprüft wird
        $this->DrivePositionWithLamellaAfterArrival($zielwert);
        IPS_LogMessage("Beschattung", "[Wochenplan] Position auf {$zielwert}% gesetzt.");
    }
    
    
    
    
    
    
    
    // Funktion wird aufgerufen wenn eine Änderung in der Benutzeroberfläche durchgeführt wird
    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case "select_modus":
                SetValue($this->GetIDForIdent("select_modus"), $Value);
                $this->Shutter_ModusSelect($Value); // eigene Methode zur Verarbeitung
                break;

                case "set_light_level_up":
                    SetValue($this->GetIDForIdent("set_light_level_up"), $Value);
                    IPS_SetProperty($this->InstanceID, 'prop_wochenplan_grenzwert_helligkeit_hochfahren', $Value);
                    IPS_ApplyChanges($this->InstanceID); // <-- WICHTIG
                    break;
                
                case "set_light_level_down":
                    SetValue($this->GetIDForIdent("set_light_level_down"), $Value);
                    IPS_SetProperty($this->InstanceID, 'prop_wochenplan_grenzwert_helligkeit_runterfahren', $Value);
                    IPS_ApplyChanges($this->InstanceID); // <-- WICHTIG
                    break;

            case "set_level_shading":
                SetValue($this->GetIDForIdent("set_level_shading"), $Value);         
                break;

            case "set_level_closed":
                SetValue($this->GetIDForIdent("set_level_closed"), $Value);
                break;
            
            case "set_auto_temp_threshold":
                SetValue($this->GetIDForIdent("set_auto_temp_threshold"), $Value);
                IPS_SetProperty($this->InstanceID, 'prop_automatik_grenzwert_temperatur', $Value);
                IPS_ApplyChanges($this->InstanceID);
                break;

            case "set_auto_light_threshold":
                SetValue($this->GetIDForIdent("set_auto_light_threshold"), $Value);
                IPS_SetProperty($this->InstanceID, 'prop_automatik_grenzwert_helligkeit', $Value);
                IPS_ApplyChanges($this->InstanceID);
                break;
            
            case "weekly_schedule":
                $this->Beschattung_Wochenplan($this->InstanceID, $Value);
                break;
            
            
            default:
                throw new Exception("Invalid Ident: " . $Ident);
        }
    }


    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        $positionID = $this->ReadPropertyInteger("prop_position");
        $lamelleID  = $this->ReadPropertyInteger("prop_lamelle");
    
        if ($Message === VM_UPDATE) {
            //
            // 1) Manuelle Sperrzeit bei Änderung von Position oder Lamelle
            //
            if ($SenderID == $positionID || $SenderID == $lamelleID) {
                $altWert = $Data[1];
                $neuWert = $Data[0];
                if ($altWert != $neuWert) {
                    $minuten = $this->ReadPropertyInteger("prop_sperrzeit");
                    if ($minuten > 0) {
                        $sperreBis = time() + ($minuten * 60);
                        $this->WriteAttributeInteger("attr_sperre_bis", $sperreBis);
                        IPS_LogMessage(
                            "Beschattung",
                            "Sperrzeit manuell bis " . date("H:i:s", $sperreBis)
                        );
                    }
                }
            }
    
            //
            // 2) Rückhub nach Erreichen der Endposition
            //
            if ($SenderID == $positionID && $this->ReadAttributeBoolean('PendingLamellen')) {
                $neuWert = $Data[0];
                $ziel    = $this->ReadAttributeInteger('attr_shading_target');
                if ($neuWert === $ziel) {
                    // Flag zurücksetzen
                    $this->WriteAttributeBoolean('PendingLamellen', false);
                    // Lamellen auf konfigurierten Winkel fahren
                    $lamellenWinkel = $this->ReadPropertyInteger("prop_lamellen_rueckhub");
                    RequestAction($lamelleID, $lamellenWinkel);
                    IPS_LogMessage(
                        "Beschattung",
                        "Lamellen‑Rückhub: Winkel {$lamellenWinkel}% nach Erreichen von {$ziel}%."
                    );
                }
            }
    
            //
            // 3) Nachprüfung Wochenplan‑Helligkeit (PendingOpen / PendingClose)
            //
            if ($SenderID == $this->ReadPropertyInteger('prop_helligkeit')) {
                $lux = GetValue($SenderID);
    
                // --- Öffnen nachprüfen ---
                if ($this->ReadAttributeBoolean('PendingOpen')) {
                    $thr = $this->ReadPropertyInteger('prop_wochenplan_grenzwert_helligkeit_hochfahren');
                    if ($lux > $thr) {
                        $this->DrivePositionWithLamellaAfterArrival(0);
                        IPS_LogMessage("Beschattung", "Nachgeprüfte Öffnung bei Lux={$lux} > {$thr}");
                        $this->WriteAttributeBoolean('PendingOpen', false);
                        $this->UnregisterMessage($SenderID, VM_UPDATE);
                    } else {
                        IPS_LogMessage("Beschattung", "Nachgeprüfte Öffnung blockiert – Lux={$lux} < {$thr}");
                    }
                }
    
                // --- Schließen nachprüfen ---
                if ($this->ReadAttributeBoolean('PendingClose')) {
                    $thr = $this->ReadPropertyInteger('prop_wochenplan_grenzwert_helligkeit_runterfahren');
                    if ($lux < $thr) {
                        $this->DrivePositionWithLamellaAfterArrival(100);
                        IPS_LogMessage("Beschattung", "Nachgeprüftes Schließen bei Lux={$lux} < {$thr}");
                        $this->WriteAttributeBoolean('PendingClose', false);
                        $this->UnregisterMessage($SenderID, VM_UPDATE);
                    } else {
                        IPS_LogMessage("Beschattung", "Nachgeprüftes Schließen blockiert – Lux={$lux} > {$thr}");
                    }
                }
            }
        }
    }
    

    public function IsAutomatikErlaubt(): bool
    {
        $planID = $this->ReadAttributeInteger("attr_HeatingPlanID");
    
        if ($planID === 0 || !IPS_EventExists($planID)) {
            IPS_LogMessage("Beschattung", "Automatikprüfung: Kein Wochenplan vorhanden.");
            return false;
        }
    
        $actionID = IPS_GetScheduleAction($planID);
        if ($actionID !== 0) {
            IPS_LogMessage("Beschattung", "Automatikprüfung: Wochenplan steht nicht auf Aktion 0.");
            return false;
        }
    
        return true;
    }



    private function DrivePositionWithLamellaAfterArrival(int $ziel): void
    {
        $positionID = $this->ReadPropertyInteger("prop_position");
        if (!IPS_VariableExists($positionID)) {
            IPS_LogMessage("Beschattung", "[Rückhub] Abbruch – Positions-ID ungültig.");
            return;
        }

        // 1) Position fahren
        RequestAction($positionID, $ziel);
        IPS_LogMessage("Beschattung", "[Wochenplan] Position auf {$ziel}% gesetzt.");

        // 2) Lamellen-Rückhub vorbereiten (wie in der Automatik)
        $ruecksatz = $this->ReadPropertyInteger("prop_lamellen_rueckhub");

        if ($this->ReadPropertyBoolean('prop_lamellen_rueckhub_aktiv')) {
            $this->WriteAttributeBoolean('PendingLamellen', true);
            $this->WriteAttributeInteger('attr_shading_target', $ziel);
            IPS_LogMessage("Beschattung","[Rückhub vorbereitet] Nach Erreichen von {$ziel}% → Lamellen auf {$ruecksatz}%.");
        }        
        
    }

    
    

    

}