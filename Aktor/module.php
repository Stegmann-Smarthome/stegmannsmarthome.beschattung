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
        $this->RegisterPropertyInteger("prop_wochenplan_grenzwert_helligkeit_runterfahren", 0);
        $this->RegisterPropertyInteger("prop_wochenplan_grenzwert_helligkeit_hochfahren", 0);
        $this->RegisterPropertyInteger("prop_automatik_grenzwert_temperatur", 0);
        $this->RegisterPropertyInteger("prop_automatik_grenzwert_helligkeit", 0);
        $this->RegisterPropertyInteger("prop_automatik_debounce_min", 10);
        $this->RegisterPropertyBoolean("prop_wochenplan_helligkeit", 0);
        $this->RegisterPropertyInteger("prop_azimut_preset", 0);
        $this->RegisterPropertyInteger("prop_azimut_min", 0);
        $this->RegisterPropertyInteger("prop_azimut_max", 360);
        $this->RegisterPropertyInteger("prop_azimut", 0);
        $this->RegisterPropertyBoolean("prop_automatikmodus_aktivieren", 0);
        $this->RegisterPropertyInteger("prop_sperrzeit", 60);
        $this->RegisterPropertyBoolean("prop_wochenplan_helligkeit_einstellungen", false);
        $this->RegisterPropertyBoolean('prop_rollo_offen_lamellen_position', false);
        $this->RegisterPropertyBoolean('prop_rollo_geschlossen_lamellen_position', false);
        $this->RegisterPropertyBoolean('prop_level_beschattung_anzeigen', true);
        $this->RegisterPropertyBoolean('prop_level_geschlossen_anzeigen', true);

        //Automatikmodus
        $this->RegisterPropertyBoolean("prop_automatikmodus_runterfahren_helligkeit", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_runterfahren_temperatur", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_runterfahren_azimut", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_hochfahren_helligkeit", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_hochfahren_temperatur", false);
        $this->RegisterPropertyBoolean("prop_automatikmodus_hochfahren_azimut", false);
        $this->RegisterPropertyBoolean("prop_automatik_grenzwerte_anzeigen", false);
        $this->RegisterPropertyBoolean("prop_automatik_grenzwerte_temperatur_anzeigen", false);
        $this->RegisterPropertyBoolean("prop_automatik_grenzwerte_helligkeit_anzeigen", false);

        // Automatik nach manueller Bedienung
        // 0 = Nie sperren
        // 1 = Benutzerdefiniert (prop_sperrzeit)
        // 2 = Bis Tagesende deaktivieren
        $this->RegisterPropertyInteger('prop_automatik_nach_manuell_aktion', 1);


        
        ############################## Erstellen von Attributen
        $this->RegisterAttributeInteger("attr_former_weekly_schedule", 0);
        $this->RegisterAttributeInteger("attr_HeatingPlanID", 0);
        $this->RegisterAttributeInteger("attr_sperre_bis", 0);
        $this->RegisterAttributeInteger("attr_automatik_aus_bis", 0);
        $this->RegisterAttributeInteger('attr_last_auto_check', 0);
        $this->RegisterAttributeBoolean("attr_last_automatik_aktiv", false);
        $this->RegisterAttributeInteger('attr_internal_move_until', 0);
        $this->RegisterAttributeInteger('attr_internal_target_position', -1);
        $this->RegisterAttributeInteger('attr_internal_target_lamelle', -1);
        $this->RegisterAttributeInteger('attr_internal_request_ts_position', 0);
        $this->RegisterAttributeInteger('attr_internal_request_ts_lamelle', 0);
        // Flag: erste Öffnung noch ausstehend
        $this->RegisterAttributeBoolean('PendingOpen', false);
        // Flag: letztes Schließen noch ausstehend
        $this->RegisterAttributeBoolean('PendingClose', false);
        // Flag: nach Endposition Rückhub auslösen
        // (entfernt)
        // Ziel‑Position merken, um im MessageSink zu prüfen
        // (entfernt)

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

        ############################## (Timer entfernt – ereignisgesteuerte Automatik)

    }

    // Überschreibt die interne IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        $showLevelShading = $this->ReadPropertyBoolean('prop_level_beschattung_anzeigen');
        $showLevelClosed = $this->ReadPropertyBoolean('prop_level_geschlossen_anzeigen');
        $shadingVarId = @$this->GetIDForIdent('set_level_shading');
        $closedVarId = @$this->GetIDForIdent('set_level_closed');

        if ($shadingVarId !== false) {
            IPS_SetHidden($shadingVarId, !$showLevelShading);
        }
        if ($closedVarId !== false) {
            IPS_SetHidden($closedVarId, !$showLevelClosed);
        }

        $changed = false;

        // Automatik: Bedingung aktiv, aber Sensor fehlt -> Bedingung(en) automatisch deaktivieren
        $hid = $this->ReadPropertyInteger('prop_helligkeit');
        if (!IPS_VariableExists($hid)) {
            if ($this->ReadPropertyBoolean('prop_automatikmodus_runterfahren_helligkeit')) {
                IPS_SetProperty($this->InstanceID, 'prop_automatikmodus_runterfahren_helligkeit', false);
                $changed = true;
            }
            if ($this->ReadPropertyBoolean('prop_automatikmodus_hochfahren_helligkeit')) {
                IPS_SetProperty($this->InstanceID, 'prop_automatikmodus_hochfahren_helligkeit', false);
                $changed = true;
            }
            if ($changed) {
                $this->LogMessage("Beschattung: Automatik: 'Helligkeit beachten' deaktiviert, da kein gueltiger Helligkeitssensor ausgewaehlt ist.", KL_MESSAGE);
            }
        }

        $tid = $this->ReadPropertyInteger('prop_temperatur');
        if (!IPS_VariableExists($tid)) {
            $changedTemp = false;
            if ($this->ReadPropertyBoolean('prop_automatikmodus_runterfahren_temperatur')) {
                IPS_SetProperty($this->InstanceID, 'prop_automatikmodus_runterfahren_temperatur', false);
                $changed = true;
                $changedTemp = true;
            }
            if ($this->ReadPropertyBoolean('prop_automatikmodus_hochfahren_temperatur')) {
                IPS_SetProperty($this->InstanceID, 'prop_automatikmodus_hochfahren_temperatur', false);
                $changed = true;
                $changedTemp = true;
            }
            if ($changedTemp) {
                $this->LogMessage("Beschattung: Automatik: 'Temperatur beachten' deaktiviert, da kein gueltiger Temperatursensor ausgewaehlt ist.", KL_MESSAGE);
            }
        }

        $aid = $this->ReadPropertyInteger('prop_azimut');
        if (!IPS_VariableExists($aid)) {
            $changedAz = false;
            if ($this->ReadPropertyBoolean('prop_automatikmodus_runterfahren_azimut')) {
                IPS_SetProperty($this->InstanceID, 'prop_automatikmodus_runterfahren_azimut', false);
                $changed = true;
                $changedAz = true;
            }
            if ($this->ReadPropertyBoolean('prop_automatikmodus_hochfahren_azimut')) {
                IPS_SetProperty($this->InstanceID, 'prop_automatikmodus_hochfahren_azimut', false);
                $changed = true;
                $changedAz = true;
            }
            if ($changedAz) {
                $this->LogMessage("Beschattung: Automatik: 'Azimut beachten' deaktiviert, da keine gueltige Azimut-Variable ausgewaehlt ist.", KL_MESSAGE);
            }
        }

        if ($changed) {
            IPS_ApplyChanges($this->InstanceID);
            return;
        }

        if ($this->ReadPropertyBoolean('prop_wochenplan_helligkeit')) {
            $hid = $this->ReadPropertyInteger('prop_helligkeit');
            if (!IPS_VariableExists($hid)) {
                $this->LogMessage("Beschattung: Wochenplan: 'Helligkeit beachten' ist aktiv, aber es ist kein gültiger Helligkeitssensor ausgewählt. Option wird deaktiviert.", KL_MESSAGE);
                IPS_SetProperty($this->InstanceID, 'prop_wochenplan_helligkeit', false);
                IPS_SetProperty($this->InstanceID, 'prop_wochenplan_helligkeit_einstellungen', false);
                IPS_ApplyChanges($this->InstanceID);
                return;
            }
        }

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

        // Sensor-Subscriptions abhängig vom Automatikmodus
        $automatikAktiv = $this->ReadPropertyBoolean("prop_automatikmodus_aktivieren");
        $hid = $this->ReadPropertyInteger('prop_helligkeit');
        $tid = $this->ReadPropertyInteger('prop_temperatur');
        $aid = $this->ReadPropertyInteger('prop_azimut');

        // Erst abmelden, dann ggf. anmelden
        if (IPS_VariableExists($hid)) { $this->UnregisterMessage($hid, VM_UPDATE); }
        if (IPS_VariableExists($tid)) { $this->UnregisterMessage($tid, VM_UPDATE); }
        if (IPS_VariableExists($aid)) { $this->UnregisterMessage($aid, VM_UPDATE); }

        if ($automatikAktiv) {
            if (IPS_VariableExists($hid)) { $this->RegisterMessage($hid, VM_UPDATE); }
            if (IPS_VariableExists($tid)) { $this->RegisterMessage($tid, VM_UPDATE); }
            if (IPS_VariableExists($aid)) { $this->RegisterMessage($aid, VM_UPDATE); }
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
        }

        // --- Alten WeeklySchedule-Timer (falls vorhanden) löschen ---
        $timerIdent = 'WeeklyScheduleTimer';
        $timerID = @IPS_GetObjectIDByIdent($timerIdent, $this->InstanceID);
        if ($timerID !== false && IPS_EventExists($timerID)) {
            IPS_DeleteEvent($timerID);
            $this->LogMessage("Beschattung: WeeklyScheduleTimer (ID $timerID) gelöscht.", KL_MESSAGE);
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
        if (@$this->ReadAttributeBoolean('PendingLamellen') === null) {
            $this->RegisterAttributeBoolean('PendingLamellen', false);
        }
        if (@$this->ReadAttributeInteger('attr_shading_target') === null) {
            $this->RegisterAttributeInteger('attr_shading_target', 0);
        }
        if (@$this->ReadAttributeInteger('attr_last_auto_check') === null) {
            $this->RegisterAttributeInteger('attr_last_auto_check', 0);
        }

        $timerID = @IPS_GetObjectIDByIdent('LamellenRueckhub', $this->InstanceID);
        if ($timerID !== false && IPS_EventExists($timerID)) {
            IPS_DeleteEvent($timerID);
            $this->LogMessage("Beschattung: Alten LamellenRueckhub-Timer (ID $timerID) gelöscht.", KL_MESSAGE);
        }

        // Backwards‑Compatibility: fehlende Attribute nachregistrieren

        $automatikAktiv = $this->ReadPropertyBoolean("prop_automatikmodus_aktivieren");
        $showAutoTempThreshold = $automatikAktiv && $this->ReadPropertyBoolean("prop_automatik_grenzwerte_temperatur_anzeigen");
        $showAutoLightThreshold = $automatikAktiv && $this->ReadPropertyBoolean("prop_automatik_grenzwerte_helligkeit_anzeigen");

        if ($showAutoTempThreshold) {
            $this->RegisterVariableInteger("set_auto_temp_threshold", "Automatik: Temperatur", [
                "PRESENTATION" => VARIABLE_PRESENTATION_SLIDER,
                "ICON" => "arrows-rotate",
                "SUFFIX" => " °C",
                'MIN' => 0,
                'MAX' => 35,
                "STEP_SIZE" => 1
            ], 15);
            $this->EnableAction("set_auto_temp_threshold");
            SetValue($this->GetIDForIdent("set_auto_temp_threshold"), $this->ReadPropertyInteger('prop_automatik_grenzwert_temperatur'));
        } else {
            if (@$this->GetIDForIdent("set_auto_temp_threshold")) {
                $this->UnregisterVariable("set_auto_temp_threshold");
            }
        }

        if ($showAutoLightThreshold) {
            $this->RegisterVariableInteger("set_auto_light_threshold", "Automatik: Helligkeit", [
                "PRESENTATION" => VARIABLE_PRESENTATION_SLIDER,
                "ICON" => "arrows-rotate",
                "SUFFIX" => " Lux",
                'MIN' => 0,
                'MAX' => 10000,
                "STEP_SIZE" => 100
            ], 16);
            $this->EnableAction("set_auto_light_threshold");
            SetValue($this->GetIDForIdent("set_auto_light_threshold"), $this->ReadPropertyInteger('prop_automatik_grenzwert_helligkeit'));
        } else {
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
            $this->LogMessage("Beschattung: Alter Wochenplan (ID {$existingPlanID}) gelöscht.", KL_MESSAGE);
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
        $modusId = @$this->GetIDForIdent('select_modus');
        $modus = ($modusId !== false) ? GetValue($modusId) : 0;
        $enableSchedule = ($modus !== 0);
        IPS_SetEventActive($heatingPlan, $enableSchedule);
        IPS_SetDisabled($heatingPlan, !$enableSchedule);
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

        $this->LogMessage("Beschattung: Neuer Wochenplan angelegt (ID {$heatingPlan}).", KL_MESSAGE);
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
                $this->LogMessage("Beschattung: Modus = Manuell -> keine Automatik aktiv", KL_MESSAGE);
                if ($planID > 0 && IPS_EventExists($planID)) {
                    IPS_SetEventActive($planID, false);     // Event abschalten
                    IPS_SetDisabled($planID, true);         // Objekt deaktivieren
                }
                // Timer entfernt: ereignisgesteuerte Automatik
                break;

            case 1: // Wochenplan
                $this->LogMessage("Beschattung: Modus = Wochenplan -> Zeitsteuerung aktiv", KL_MESSAGE);
                if ($planID > 0 && IPS_EventExists($planID)) {
                    IPS_SetEventActive($planID, true);
                    IPS_SetDisabled($planID, false);
                }
                // Timer entfernt: ereignisgesteuerte Automatik
                break;

            case 2: // Automatik
                $this->LogMessage("Beschattung: Modus = Automatik -> Sensorsteuerung aktiv", KL_MESSAGE);
                if ($planID > 0 && IPS_EventExists($planID)) {
                    IPS_SetEventActive($planID, true);
                    IPS_SetDisabled($planID, false);
                }
                // Timer entfernt: Automatik wird durch Sensor-Events ausgelöst
                break;

            default:
                $this->LogMessage("Beschattung: Unbekannter Modus: $modus", KL_MESSAGE);
                break;
        }
    }

    public function CheckAutoShading()
    {
        $this->LogMessage("Beschattung: Automatikpruefung manuell ausgelöst.", KL_MESSAGE);

        // Automatik ggf. bis Tagesende deaktiviert
        $now = time();
        $automatikAusBis = $this->ReadAttributeInteger("attr_automatik_aus_bis");
        if ($automatikAusBis > $now) {
            $rest = $automatikAusBis - $now;
            $this->LogMessage("Beschattung: [Automatikpruefung] Abbruch - Automatik bis Tagesende deaktiviert (noch {$rest} Sek.).", KL_MESSAGE);
            return;
        }

        // Sperrzeit prüfen
        $sperreBis = $this->ReadAttributeInteger("attr_sperre_bis");
        if ($sperreBis > $now) {
            $rest = $sperreBis - $now;
            $this->LogMessage("Beschattung: [Automatikpruefung] Abbruch - gesperrt fuer weitere {$rest} Sek.", KL_MESSAGE);
            return;
        }
    
        // Wochenplan prüfen
        if (!$this->IsAutomatikErlaubt()) {
            $this->LogMessage("Beschattung: [Automatikpruefung] Abbruch - Wochenplan steht nicht auf Aktion 0 (Offen).", KL_MESSAGE);
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
    $hasStartCondition =
        $this->ReadPropertyBoolean("prop_automatikmodus_runterfahren_helligkeit") ||
        $this->ReadPropertyBoolean("prop_automatikmodus_runterfahren_temperatur") ||
        $this->ReadPropertyBoolean("prop_automatikmodus_runterfahren_azimut");
    if (!$hasStartCondition) {
        $this->LogMessage("Beschattung: [Automatikpruefung] Abbruch - Keine Start-Bedingung aktiv (Helligkeit/Temperatur/Azimut).", KL_MESSAGE);
        return;
    }

    $runterfahrenErlaubt = true;

    if ($this->ReadPropertyBoolean("prop_automatikmodus_runterfahren_helligkeit") && $checkLux) {
        $luxGrenzeRunter = $this->ReadPropertyInteger("prop_automatik_grenzwert_helligkeit");
        if ($lux < $luxGrenzeRunter) {
            $this->LogMessage("Beschattung: [Startbedingung Runterfahren] Helligkeit: $lux lx < $luxGrenzeRunter lx -> JA.", KL_MESSAGE);
        } else {
            $this->LogMessage("Beschattung: [Startbedingung Runterfahren] Helligkeit: $lux lx >= $luxGrenzeRunter lx -> NEIN.", KL_MESSAGE);
            $runterfahrenErlaubt = false;
        }
    }

    if ($this->ReadPropertyBoolean("prop_automatikmodus_runterfahren_temperatur") && $checkTemp) {
        $tempGrenze = $this->ReadPropertyInteger("prop_automatik_grenzwert_temperatur");
        if ($temp >= $tempGrenze) {
            $this->LogMessage("Beschattung: [Startbedingung Runterfahren] Temperatur: $temp >= $tempGrenze -> JA.", KL_MESSAGE);
        } else {
            $this->LogMessage("Beschattung: [Startbedingung Runterfahren] Temperatur: $temp < $tempGrenze -> NEIN.", KL_MESSAGE);
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
            $this->LogMessage("Beschattung: [Startbedingung Runterfahren] Azimut: $azimut innerhalb von $azimutMin-$azimutMax -> JA.", KL_MESSAGE);
        } else {
            $this->LogMessage("Beschattung: [Startbedingung Runterfahren] Azimut: $azimut ausserhalb von $azimutMin-$azimutMax -> NEIN.", KL_MESSAGE);
            $runterfahrenErlaubt = false;
        }
    }

    // Runterfahren wenn erlaubt
    if ($runterfahrenErlaubt) {
        // 1) Schatten-Position anfahren
        $this->WriteAttributeInteger('attr_internal_target_position', (int)$levelBeschattung);
        $this->WriteAttributeInteger('attr_internal_request_ts_position', time());
        RequestAction($positionID, $levelBeschattung);
        // Rückhub entfernt
        
        
    }



    // -----------------------------------
    // HOCHFAHREN (ODER-Verknüpfung)
    // -----------------------------------
    $hasEndCondition =
        $this->ReadPropertyBoolean("prop_automatikmodus_hochfahren_helligkeit") ||
        $this->ReadPropertyBoolean("prop_automatikmodus_hochfahren_temperatur") ||
        $this->ReadPropertyBoolean("prop_automatikmodus_hochfahren_azimut");
    if (!$hasEndCondition) {
        $this->LogMessage("Beschattung: [Automatikpruefung] Abbruch - Keine End-Bedingung aktiv (Helligkeit/Temperatur/Azimut).", KL_MESSAGE);
        return;
    }

    $hochfahren = false;

    if ($this->ReadPropertyBoolean("prop_automatikmodus_hochfahren_helligkeit") && isset($lux)) {
        $grenze = $this->ReadPropertyInteger("prop_automatik_grenzwert_helligkeit");
        if ($lux < $grenze) {
            $hochfahren = true;
            $this->LogMessage("Beschattung: [Endbedingung Hochfahren] Helligkeit: $lux lx < {$grenze} lx -> JA.", KL_MESSAGE);
        }
    }

    if ($this->ReadPropertyBoolean("prop_automatikmodus_hochfahren_temperatur") && isset($temp)) {
        $grenze = $this->ReadPropertyInteger("prop_automatik_grenzwert_temperatur");
        if ($temp < $grenze) {
            $hochfahren = true;
            $this->LogMessage("Beschattung: [Endbedingung Hochfahren] Temperatur: $temp < {$grenze} -> JA.", KL_MESSAGE);
        }
    }

    if ($this->ReadPropertyBoolean("prop_automatikmodus_hochfahren_azimut") && isset($azimut)) {
        $min = $this->ReadPropertyInteger("prop_azimut_min");
        $max = $this->ReadPropertyInteger("prop_azimut_max");
        $außerhalb = ($min > $max) ? !($azimut >= $min || $azimut <= $max) : !($azimut >= $min && $azimut <= $max);
        if ($außerhalb) {
            $hochfahren = true;
            $this->LogMessage("Beschattung: [Endbedingung Hochfahren] Azimut: $azimut ausserhalb von $min-$max -> JA.", KL_MESSAGE);
        }
    }

    if ($hochfahren) {
        $this->WriteAttributeInteger('attr_internal_target_position', 0);
        $this->WriteAttributeInteger('attr_internal_request_ts_position', time());
        RequestAction($positionID, 0);
        $this->LogMessage("Beschattung: [Automatik] Hochgefahren.", KL_MESSAGE);
        return;
    }



    }
    
    

    public function LamellenRueckhub()
    {
        $this->SetTimerInterval("LamellenRueckhub", 0); // Timer stoppen
        $positionID = $this->ReadPropertyInteger("prop_position");
    
        if (!IPS_VariableExists($positionID)) {
            $this->LogMessage("Beschattung: Rueckhub-Ziel konnte nicht ausgefuehrt werden - Aktor fehlt.", KL_ERROR);
            return;
        }
    
        $ziel = $this->ReadAttributeInteger("attr_lamellen_soll");
        $this->WriteAttributeInteger('attr_internal_target_position', (int)$ziel);
        $this->WriteAttributeInteger('attr_internal_request_ts_position', time());
        RequestAction($positionID, $ziel);
        $this->LogMessage("Beschattung: Lamellen-Rueckhub ausgefuehrt auf {$ziel}%.", KL_MESSAGE);
    }

    // Funktion zum Rücksetzen der Speere
    public function ResetSperre()
    {
        $this->WriteAttributeInteger("attr_sperre_bis", 0);
        $this->WriteAttributeInteger("attr_automatik_aus_bis", 0);
        $this->LogMessage("Beschattung: Automatik-Sperre manuell zurueckgesetzt.", KL_MESSAGE);
    }
    

    public function Beschattung_Wochenplan(int $id, int $actionID) {
        $this->LogMessage("Beschattung: Wochenplan: Aktion $actionID wurde ausgeloest", KL_MESSAGE);
    
        $positionID = $this->ReadPropertyInteger("prop_position");
        $planID     = $this->ReadAttributeInteger("attr_HeatingPlanID");
        $zielwert   = ($actionID == 0) ? 0 : 100;
    
        if ($this->ReadPropertyBoolean("prop_wochenplan_helligkeit")
         && $this->IsFirstOrLastScheduleActionToday($planID, $actionID)) {

            $hid = $this->ReadPropertyInteger("prop_helligkeit");
            if (!IPS_VariableExists($hid)) {
                $this->LogMessage("Beschattung: [Wochenplan] Helligkeitspruefung aktiv, aber Helligkeitssensor ungueltig. Pruefung wird uebersprungen.", KL_MESSAGE);
                $this->WriteAttributeBoolean('PendingOpen', false);
                $this->WriteAttributeBoolean('PendingClose', false);
                $lux = null;
            } else {
                $lux = GetValue($hid);
            }

            if ($lux === null) {
                $this->DrivePositionWithLamellaAfterArrival($zielwert);
                $this->LogMessage("Beschattung: [Wochenplan] Position auf {$zielwert}% gesetzt.", KL_MESSAGE);
                return;
            }

            if ($actionID == 0) {
                // --- Öffnen: blockieren, wenn zu dunkel ---
                $threshold = $this->ReadPropertyInteger("prop_wochenplan_grenzwert_helligkeit_hochfahren");
                if ($lux < $threshold) {
                    $this->LogMessage("Beschattung: [Wochenplan] Hochfahren blockiert - Lux={$lux} < {$threshold}", KL_MESSAGE);
                    // Nachprüfung aktivieren
                    $this->WriteAttributeBoolean('PendingOpen', true);
                    $hid = $this->ReadPropertyInteger('prop_helligkeit');
                    if (IPS_VariableExists($hid)) {
                        $this->RegisterMessage($hid, VM_UPDATE);
                    }
                    return;
                }
                $this->LogMessage("Beschattung: [Wochenplan] Hochfahren erlaubt - Lux={$lux} >= {$threshold}", KL_MESSAGE);
            }
            else {
                // --- Schließen: blockieren, wenn zu dunkel ---
                $threshold = $this->ReadPropertyInteger("prop_wochenplan_grenzwert_helligkeit_runterfahren");
                if ($lux > $threshold) {
                    $this->LogMessage("Beschattung: [Wochenplan] Runterfahren blockiert - Lux={$lux} > {$threshold}", KL_MESSAGE);
                    // Nachprüfung aktivieren
                    $this->WriteAttributeBoolean('PendingClose', true);
                    $hid = $this->ReadPropertyInteger('prop_helligkeit');
                    if (IPS_VariableExists($hid)) {
                        $this->RegisterMessage($hid, VM_UPDATE);
                    }
                    return;
                }
                $this->LogMessage("Beschattung: [Wochenplan] Runterfahren erlaubt - Lux={$lux} >= {$threshold}", KL_MESSAGE);
            }
        }
    
        // Ausführung, wenn nicht blockiert oder Helligkeit nicht geprüft wird
        $this->DrivePositionWithLamellaAfterArrival($zielwert);
        $this->LogMessage("Beschattung: [Wochenplan] Position auf {$zielwert}% gesetzt.", KL_MESSAGE);
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
            // 0) Ereignisgesteuerte Automatik mit Debounce (Sensor-Updates)
            $isAutomatik = $this->ReadPropertyBoolean('prop_automatikmodus_aktivieren');
            if ($isAutomatik) {
                $hid = $this->ReadPropertyInteger('prop_helligkeit');
                $tid = $this->ReadPropertyInteger('prop_temperatur');
                $aid = $this->ReadPropertyInteger('prop_azimut');
                $isSensorUpdate = ($SenderID == $hid) || ($SenderID == $tid) || ($SenderID == $aid);
                if ($isSensorUpdate) {
                    $now = time();
                    $last = $this->ReadAttributeInteger('attr_last_auto_check');
                    $debMin = max(0, (int)$this->ReadPropertyInteger('prop_automatik_debounce_min'));
                    if ($now - $last >= ($debMin * 60)) {
                        $this->CheckAutoShading();
                        $this->WriteAttributeInteger('attr_last_auto_check', $now);
                    }
                }
            }
            //
            // 1) Manuelle Sperrzeit bei Änderung von Position oder Lamelle
            //
            if ($SenderID == $positionID || $SenderID == $lamelleID) {
                $now = time();
                $neuWert = $Data[0] ?? (IPS_VariableExists($SenderID) ? GetValue($SenderID) : null);

                $isInternal = false;
                $maxAge = 60;
                if (is_int($neuWert) || is_float($neuWert) || is_string($neuWert)) {
                    if ($SenderID == $positionID) {
                        $target = $this->ReadAttributeInteger('attr_internal_target_position');
                        $ts = $this->ReadAttributeInteger('attr_internal_request_ts_position');
                        if ($target !== -1 && ($ts + $maxAge) >= $now && ((string)$neuWert === (string)$target)) {
                            $isInternal = true;
                        }
                    } else {
                        $target = $this->ReadAttributeInteger('attr_internal_target_lamelle');
                        $ts = $this->ReadAttributeInteger('attr_internal_request_ts_lamelle');
                        if ($target !== -1 && ($ts + $maxAge) >= $now && ((string)$neuWert === (string)$target)) {
                            $isInternal = true;
                        }
                    }
                }

                if ($isInternal) {
                    return;
                }

                $aktion = (int)$this->ReadPropertyInteger('prop_automatik_nach_manuell_aktion');
                if ($aktion === 2) {
                    $endOfDay = strtotime('tomorrow 00:00:00') - 1;
                    $this->WriteAttributeInteger('attr_automatik_aus_bis', $endOfDay);
                    $this->LogMessage("Automatik nach manueller Bedienung bis Tagesende deaktiviert bis " . date("H:i:s", $endOfDay), KL_MESSAGE);
                } elseif ($aktion === 1) {
                    $minuten = (int)$this->ReadPropertyInteger('prop_sperrzeit');
                    if ($minuten > 0) {
                        $sperreBis = time() + ($minuten * 60);
                        $this->WriteAttributeInteger('attr_sperre_bis', $sperreBis);
                        $this->LogMessage("Sperrzeit manuell bis " . date("H:i:s", $sperreBis), KL_MESSAGE);
                    }
                }
            }
    

            if ($SenderID == $positionID) {
                $aktuellerWert = $Data[0];
                if ($aktuellerWert === 0 && $this->ReadPropertyBoolean('prop_rollo_offen_lamellen_position')) {
                    if (IPS_VariableExists($lamelleID)) {
                        $this->WriteAttributeInteger('attr_internal_target_lamelle', 0);
                        $this->WriteAttributeInteger('attr_internal_request_ts_lamelle', time());
                        RequestAction($lamelleID, 0);
                    }
                }
                if ($aktuellerWert === 100 && $this->ReadPropertyBoolean('prop_rollo_geschlossen_lamellen_position')) {
                    if (IPS_VariableExists($lamelleID)) {
                        $this->WriteAttributeInteger('attr_internal_target_lamelle', 100);
                        $this->WriteAttributeInteger('attr_internal_request_ts_lamelle', time());
                        RequestAction($lamelleID, 100);
                    }
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
                        $this->LogMessage("Nachgepruefte Oeffnung bei Lux={$lux} > {$thr}", KL_MESSAGE);
                        $this->WriteAttributeBoolean('PendingOpen', false);
                        $this->UnregisterMessage($SenderID, VM_UPDATE);
                    } else {
                        $this->LogMessage("Nachgepruefte Oeffnung blockiert - Lux={$lux} < {$thr}", KL_MESSAGE);
                    }
                }
    
                // --- Schließen nachprüfen ---
                if ($this->ReadAttributeBoolean('PendingClose')) {
                    $thr = $this->ReadPropertyInteger('prop_wochenplan_grenzwert_helligkeit_runterfahren');
                    if ($lux < $thr) {
                        $this->DrivePositionWithLamellaAfterArrival(100);
                        $this->LogMessage("Nachgeprueftes Schliessen bei Lux={$lux} < {$thr}", KL_MESSAGE);
                        $this->WriteAttributeBoolean('PendingClose', false);
                        $this->UnregisterMessage($SenderID, VM_UPDATE);
                    } else {
                        $this->LogMessage("Nachgeprueftes Schliessen blockiert - Lux={$lux} > {$thr}", KL_MESSAGE);
                    }
                }
            }
        }
    }
    

    public function IsAutomatikErlaubt(): bool
    {
        $planID = $this->ReadAttributeInteger("attr_HeatingPlanID");

        if ($planID === 0 || !IPS_EventExists($planID)) {
            $this->LogMessage("Automatikpruefung: Kein Wochenplan vorhanden.", KL_MESSAGE);
            return false;
        }

        $eventInfo = IPS_GetEvent($planID);
        $actionID = $eventInfo['LastActionID'] ?? null;
        if ($actionID === null) {
            $this->LogMessage("Automatikpruefung: Wochenplan-Aktion konnte nicht ermittelt werden (LastActionID nicht verfuegbar).", KL_MESSAGE);
            return true;
        }

        if ($actionID !== 0) {
            $this->LogMessage("Automatikpruefung: Wochenplan steht nicht auf Aktion 0.", KL_MESSAGE);
            return false;
        }
    
        return true;
    }



    private function DrivePositionWithLamellaAfterArrival(int $ziel): void
    {
        $positionID = $this->ReadPropertyInteger("prop_position");
        if (!IPS_VariableExists($positionID)) {
            $this->LogMessage("[Rueckhub] Abbruch - Positions-ID ungueltig.", KL_ERROR);
            return;
        }

        // 1) Position fahren
        $this->WriteAttributeInteger('attr_internal_target_position', (int)$ziel);
        $this->WriteAttributeInteger('attr_internal_request_ts_position', time());
        RequestAction($positionID, $ziel);
        $this->LogMessage("[Wochenplan] Position auf {$ziel}% gesetzt.", KL_MESSAGE);
    }



}