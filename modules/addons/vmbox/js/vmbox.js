function VMBOX(WebSocketAddress, params) {
  this.Status = ""; // set by this.onStatusChanged
  this.IsSuspended = false; // set by this.onServiceSuspendedChanged
  this.Username =
    params && params.Username ? params.Username : "root/Administrator";
  this.Password =
    params && params.Password ? params.Password : i18n.Translate("Unknown"); // set by this.onPasswordChanged
  this.IpAddress =
    params && params.IpAddress ? params.IpAddress : i18n.Translate("Unknown"); // set by this.onPasswordChanged
  this.OsType = "";
  this.TemplateName = "";
  this.OsLogo = "";
  this.Hostname = "";
  this.UptimeSeconds = 0;

  this._defaultActionsHTML = document.getElementById("serverActions").innerHTML;
  this._actionInProgress = -1;
  this._isActionDetailsForcedToClosed = false;
  this._DoIfNoActionInProgress = (cb) => {
    cb = typeof cb === "function" ? cb : () => { };
    if (this._actionInProgress == true) {
      this.OpenActionDetails();
    } else cb();
  };
  this._getVmScreenshotTimeoutId = 0;
  this._getMonitoringDataTimeoutId = 0;
  this._OpenedTab = "";
  this._OpenedUsageType = ""; // cpuGraph, ramGraph

  this.IsoFiles = [];
  this.IsoSelect = null;
  this.SelectedIsoId = null;

  this.PlanTemplates = [];
  this.PlanTemplateSelect = null;
  this.SelectedPlanTemplate = null;

  /*  incoming message events  */

  this.onPanelDetailsChanged = (pnlDetailsRaw) => {
    var pnlDetails = JSON.parse(pnlDetailsRaw);
    this.onStatusChanged(pnlDetails.Status);
    this.onServiceSuspendedChanged(pnlDetails.IsSuspended);
    this.onPasswordChanged(pnlDetails.OsPassword);
    this.onIpAddressChanged(pnlDetails.IpAddress);
    this.onOsTypeChanged(pnlDetails.OsType, pnlDetails.TemplateName);
    this.onHostnameChanged(pnlDetails.Hostname);
    this.onUptimeChanged(pnlDetails.UptimeSeconds);
    this.onActionsChanged(pnlDetails.Actions);
  };

  this.onPanelScriptsChanged = (pnlScriptsRaw) => {
    var pnlScripts = JSON.parse(pnlScriptsRaw);
    if (pnlScripts.Scripts) {
      Set_Vmbox_Scripts(pnlScripts.Scripts);
    }
  }

  this.onStatusChanged = (newStatus) => {
    if (this.Status === newStatus) {
      return false;
    }
    var newStatusColor = "#000";
    if (newStatus == "PoweredOn") {
      EnableElementById("wmks");
      ShowElementById("remoteConnection");
    } else {
      DisableElementById("wmks");
      HideElementById("remoteConnection");
    }
    switch (newStatus) {
      case "Registered":
        newStatusColor = "#00c";
        break;
      case "Deployed":
        newStatusColor = "#00c";
        break;
      case "PoweredOn":
        newStatusColor = "#0c0";
        break;
      case "PoweredOff":
        newStatusColor = "#e00";
        break;
      case "VmSuspended":
        newStatusColor = "#ee0";
        break;
      case "Deleted":
        newStatusColor = "#505";
        break;
      case "NotFound":
        newStatusColor = "#000";
        break;
      case "Terminated":
        newStatusColor = "#aaa";
        break;
      case "Pending for PowerOn":
        newStatusColor = "#aaa";
        break;
      case "Pending for PowerOff":
        newStatusColor = "#aaa";
        break;
      case "Pending for PowerReset":
        newStatusColor = "#aaa";
        break;
      case "Pending for InsertISO":
        newStatusColor = "#aaa";
        break;
      case "Pending for EjectISO":
        newStatusColor = "#aaa";
        break;
      case "Pending for VmSuspend":
        newStatusColor = "#aaa";
        break;
      case "Pending for Delete":
        newStatusColor = "#aaa";
        break;
      case "Pending for Deploy":
        newStatusColor = "#aaa";
        break;
      case "Pending for SetupOS":
        newStatusColor = "#aaa";
        break;
      default:
        newStatusColor = "#aaa";
    }
    document.getElementById("serverStatus").innerHTML = i18n.Translate(
      newStatus
    );
    document.getElementById("serverStatus").style.color = newStatusColor;
    if (
      newStatus === "PoweredOn" ||
      (this.Status === "PoweredOn" && newStatus.startsWith("Pending"))
    ) {
      // start loading vm screenshot
      this.GetVmScreenshot();
    } else if (this._getVmScreenshotTimeoutId !== 0) {
      // stop loading vm screenshot
      clearTimeout(this._getVmScreenshotTimeoutId);
      this._getVmScreenshotTimeoutId = 0;
    }
    this.Status = newStatus;
    return true;
  };

  this.onServiceSuspendedChanged = (isSuspended) => {
    if (this.IsSuspended === isSuspended) {
      return false;
    }
    if (isSuspended) {
      ShowMessage(i18n.Translate("Service is suspended!"), "warning");
    } else {
      document.getElementById("vmb_panel_msg").classList.add("hidden");
    }
    return true;
  };

  this.onPasswordChanged = (newPassword) => {
    if (this.Password === newPassword) {
      return false;
    }
    this.Password = newPassword;
    return true;
  };

  this.onIpAddressChanged = (newIpAddress) => {
    if (newIpAddress !== "Not Assigned" && this.IpAddress !== newIpAddress) {
      this.IpAddress = newIpAddress;
      document.getElementById("serverIpAddress").innerHTML = newIpAddress;
      return true;
    }
    return false;
  };

  this.onOsTypeChanged = (OsType, TemplateName) => {
    if (this.OsType === OsType && this.TemplateName === TemplateName) {
      return false;
    }
    this.OsType = OsType;
    this.TemplateName = TemplateName;
    document.getElementById("templateName").innerHTML = this.TemplateName;
    if (OsLogos && OsTags) {
      var newOsLogo = "";
      if (OsTags[TemplateName]) {
        newOsLogo = OsTags[TemplateName];
      } else if (OsTags[OsType]) {
        newOsLogo = OsTags[OsType];
      } else if (OsLogos["other"]) {
        newOsLogo = OsLogos["other"];
      } else {
        return true;
      }
      if (this.OsLogo !== newOsLogo) {
        this.OsLogo = newOsLogo;
        document.getElementById("OsLogo").src = this.OsLogo;
      }
    }
    return true;
  };

  this.onHostnameChanged = (Hostname) => {
    if (this.Hostname === Hostname) {
      return false;
    }
    this.Hostname = Hostname;
    document.getElementById("serverHostname").innerHTML = this.Hostname;
    return true;
  };

  this.onUptimeChanged = (Uptime) => {
    if (this.UptimeSeconds === Uptime) {
      return false;
    }
    this.UptimeSeconds = Uptime;
    var uptimeStr = "-";
    if (Uptime < 60) {
      // "52s"
      uptimeStr = `${Uptime}s`;
    } else if (Uptime < 3600) {
      // "16m 31s"
      uptimeStr = `${parseInt(Uptime / 60)}m ${Uptime % 60}s`;
    } else if (Uptime < 24 * 3600) {
      // "16h 47m"
      uptimeStr = `${parseInt(Uptime / 3600)}h ${parseInt(Uptime / 60) % 60}m`;
    } else {
      // "16day(s) 3hour(s)"
      var days = parseInt(Uptime / (24 * 3600));
      var hours = parseInt(Uptime / 3600) % 24;
      uptimeStr = `${days} day${days > 1 ? "s" : ""} and ${hours} hour${hours > 1 ? "s" : ""
        }`;
    }
    document.getElementById("serverUptime").innerHTML = uptimeStr;
    return true;
  };

  this.onActionsChanged = (ActionNames) => {
    // ActionNames must be an array of actions name
    document.getElementById(
      "serverActions"
    ).innerHTML = this._defaultActionsHTML;
    if (this.Status == "PoweredOn") {
      EnableElementById("wmks");
      ShowElementById("remoteConnection");
    } else {
      DisableElementById("wmks");
      HideElementById("remoteConnection");
    }

    HideElementById("OsInstallation");
    HideElementById("InsertISO");
    HideElementById("EjectISO");

    ActionNames.forEach((action) => {
      var newButton = document.createElement("img");
      newButton.id = action;
      newButton.dataset["action"] = action;
      newButton.classList.add("serverAction");
      newButton.classList.add("otherServerAction");
      switch (action) {
        case "PowerOn":
          EnableElementById("pwrOn");
          document.getElementById("pwrOn").onclick = (evt) => {
            this._DoIfNoActionInProgress(() => {
              this._actionInProgress = true;
              this._isActionDetailsForcedToClosed = false;
              this._sendCommand("PowerOn");
            });
          };
          return; // no need to append new button
        case "PowerOff":
          EnableElementById("pwrOff");
          document.getElementById("pwrOff").onclick = (evt) => {
            this._DoIfNoActionInProgress(() => {
              this.OpenConfirmBox(
                i18n.Translate("You are about to Power off your server..."),
                () => {
                  this._actionInProgress = true;
                  this._isActionDetailsForcedToClosed = false;
                  this._sendCommand("PowerOff");
                }
              );
            });
          };
          return; // no need to append new button
        case "PowerReset":
          EnableElementById("pwrRst");
          document.getElementById("pwrRst").onclick = (evt) => {
            this._DoIfNoActionInProgress(() => {
              this.OpenConfirmBox(
                i18n.Translate("You are about to Restart your server..."),
                () => {
                  this._actionInProgress = true;
                  this._isActionDetailsForcedToClosed = false;
                  this._sendCommand("PowerReset");
                }
              );
            });
          };
          return; // no need to append new button
        case "VmSuspend":
          EnableElementById("vmSuspend");
          document.getElementById("vmSuspend").onclick = (evt) => {
            this._DoIfNoActionInProgress(() => {
              this.OpenConfirmBox(
                i18n.Translate(
                  "You are about to Suspend (Hibernate) your server..."
                ),
                () => {
                  this._actionInProgress = true;
                  this._isActionDetailsForcedToClosed = false;
                  this._sendCommand("VmSuspend");
                }
              );
            });
          };
          return; // no need to append new button
        case "EditPlanTemplate":
          ShowElementById("OsInstallation");
          this._sendObject({
            Type: "GetPlanTemplates",
          });
          document.getElementById("OsInstallation").onclick = (evt) => {
            if (this._actionInProgress == true) {
              this.OpenActionDetails();
            } else {
              if (
                !this.PlanTemplates ||
                typeof this.PlanTemplates.length != "number" ||
                this.PlanTemplates.length < 1
              ) {
                alert(i18n.Translate("No OS Templates available now."));
                return;
              }
              this.OpenConfirmBox(
                this.PlanTemplateSelect,
                () => {
                  if (
                    !this.PlanTemplateSelect.selectedOptions ||
                    this.PlanTemplateSelect.selectedOptions.length < 1 ||
                    this.PlanTemplateSelect.selectedOptions[0].value == "null"
                  ) {
                    alert(i18n.Translate("Please select a Template first."));
                    return;
                  }
                  this._actionInProgress = true;
                  this._isActionDetailsForcedToClosed = false;
                  this._sendObject({
                    Type: "EditPlanTemplate",
                    Param: this.PlanTemplateSelect.selectedOptions[0].value,
                  });
                },
                {
                  title: i18n.Translate("Select OS Template..."),
                  yesBtnText: i18n.Translate("Install Selected"),
                  noBtnText: i18n.Translate("Cancel"),
                }
              );
            }
          };
          return;
        case "InsertIso":
          ShowElementById("InsertISO");
          this._sendObject({
            Type: "GetCdDrives",
          });
          document.getElementById("InsertISO").onclick = (evt) => {
            if (this._actionInProgress == true) {
              this.OpenActionDetails();
            } else {
              if (
                !this.IsoFiles ||
                typeof this.IsoFiles.length != "number" ||
                this.IsoFiles.length < 1
              ) {
                alert(i18n.Translate("No ISO files available now."));
                return;
              }
              this.OpenConfirmBox(
                this.IsoSelect,
                () => {
                  if (
                    !this.IsoSelect.selectedOptions ||
                    this.IsoSelect.selectedOptions.length < 1 ||
                    this.IsoSelect.selectedOptions[0].value == "null"
                  ) {
                    alert(i18n.Translate("Please select a ISO file first."));
                    return;
                  }
                  this._actionInProgress = true;
                  this._isActionDetailsForcedToClosed = false;
                  this._sendObject({
                    Type: "InsertISO",
                    Param: this.IsoSelect.selectedOptions[0].value,
                  });
                },
                {
                  title: i18n.Translate("Select ISO file..."),
                  yesBtnText: i18n.Translate("Insert Selected"),
                  noBtnText: i18n.Translate("Cancel"),
                }
              );
            }
          };
          return;
        case "EjectIso":
          ShowElementById("EjectISO");
          document.getElementById("EjectISO").onclick = (evt) => {
            this._DoIfNoActionInProgress(() => {
              this.OpenConfirmBox(
                i18n.Translate(
                  "You are about to eject cd-rom devices from your server..."
                ),
                () => {
                  this._actionInProgress = true;
                  this._isActionDetailsForcedToClosed = false;
                  this._sendCommand("EjectISO");
                }
              );
            });
          };
          return;
        case "Delete":
          newButton.innerHTML = i18n.Translate("Delete");
          newButton.classList.add("red");
          newButton.onclick = (evt) => {
            if (this._actionInProgress == true) {
              this.OpenActionDetails();
            } else {
              this.OpenConfirmBox("شما در حال حذف سرور هستید", () => {
                this._actionInProgress = true;
                this._isActionDetailsForcedToClosed = false;
                this._sendCommand("Delete");
              });
            }
          };
          break;
        case "Deploy":
          newButton.innerHTML = i18n.Translate("Deploy");
          newButton.classList.add("blue");
          newButton.onclick = (evt) => {
            if (this._actionInProgress == true) {
              this.OpenActionDetails();
            } else {
              this.OpenConfirmBox(
                "شما در حال راه اندازی مجدد سرور هستید",
                () => {
                  this._actionInProgress = true;
                  this._isActionDetailsForcedToClosed = false;
                  this._sendCommand("Deploy");
                }
              );
            }
          };
          break;
        default:
          return;
      }
      document.getElementById("serverActions").appendChild(newButton);
    });
  };

  /* Handle incoming messages from the server
    Message: {
      code    Number  { 400 }
      content String
    }
  */
  this.HandleMessage = (Message) => {
    if (!Message.code || typeof Message.code != "number") return;
    switch (Message.code) {
      case 400:
        alert(i18n.Translate("Operation failed."));
        break;
    }
    console.log(Message.content);
  };

  /* Handle Action Details Upadtes
    ActionDetails: {
      steps Array_of{
        name        String
        done        Boolean
        percentage  Number
      }
    }
  */
  this.HandleAction = (ActionDetails) => {
    if (!ActionDetails.steps) return;
    if (!this._isActionDetailsForcedToClosed) {
      this.OpenActionDetails(ActionDetails.name);
    }
    var actionModalTitle = document.querySelector(
      "#actionDetailsModal #modalName"
    );
    if (actionModalTitle) {
      actionModalTitle.innerHTML = ActionDetails.name;
    }
    var actionModalContent = document.querySelector(
      "#actionDetailsModal #modalContent"
    );
    if (!actionModalContent) {
      actionModalContent = document.createElement("div");
    }
    actionModalContent.innerHTML = "";
    var IsLastStepDone = true;
    ActionDetails.steps.forEach((step, i) => {
      if (!step.done) {
        IsLastStepDone = false;
      }
      var newRow = document.createElement("div");
      newRow.id = "Act" + i;
      newRow.classList.add("action-row");
      var ActPercent = "";
      if (step.done) {
        var doneImg = document.createElement("img");
        doneImg.id = "ActDone" + i;
        doneImg.classList.add("doneActionIcon");
        doneImg.style.padding = "7px";
        doneImg.style.width = "36px";
        doneImg.src = "modules/servers/vmbox/assets/checked.png";
        newRow.appendChild(doneImg);
      } else if (step.percentage >= 0) {
        ActPercent = " " + step.percentage + "%";
        var laLoader = document.createElement("div");
        laLoader.id = "ActLoading" + i;
        laLoader.classList.add("laloader");
        newRow.appendChild(laLoader);
      } else {
        var laLoader = document.createElement("div");
        laLoader.id = "ActWaiting" + i;
        laLoader.classList.add("laloader");
        laLoader.style.opacity = 0;
        newRow.appendChild(laLoader);
      }
      var stepName = document.createElement("div");
      stepName.id = "StepName" + i;
      stepName.classList.add("action-step");
      stepName.innerHTML = i18n.Translate(step.name) + ActPercent;
      newRow.appendChild(stepName);
      actionModalContent.appendChild(newRow);
    });
    if (IsLastStepDone) {
      this._actionInProgress = false;
      var newRow = document.createElement("div");
      newRow.id = "CloseDiv";
      newRow.classList.add("action-row");
      var closeButton = document.createElement("button");
      closeButton.style.margin = "auto";
      closeButton.innerHTML = i18n.Translate("Close");
      closeButton.onclick = () => {
        this.CloseActionDetails();
      };
      newRow.appendChild(closeButton);
      actionModalContent.appendChild(newRow);
    }
  };

  /* Handle incoming Plan Templates List
    PlanTemplates: Array_of{
      Name  string  NotNull
      Id    string  NotNull
    }
  */
  this.HandlePlanTemplatesList = (PlanTemplates) => {
    PlnTmplts = JSON.parse(PlanTemplates);
    if (typeof PlnTmplts.length != "number") {
      return;
    }
    this.PlanTemplateSelect = document.createElement("select");
    this.PlanTemplateSelect.style.width = "100%";
    this.PlanTemplateSelect.options[
      this.PlanTemplateSelect.options.length
    ] = new Option(i18n.Translate("Select an Option..."), "null", true, true);
    PlnTmplts.forEach((drive) => {
      var Op = new Option(drive.Name, drive.Id, false, false);
      this.PlanTemplateSelect.options[
        this.PlanTemplateSelect.options.length
      ] = Op;
    });
    this.PlanTemplates = PlnTmplts;
  };

  /* Handle incoming CD Drives List
    CdDrives: Array_of{
      Name  string  NotNull
      Id    string  NotNull
    }
  */
  this.HandleCdDrivesList = (CdDrives) => {
    Drives = JSON.parse(CdDrives);
    if (typeof Drives.length != "number") {
      return;
    }
    this.IsoSelect = document.createElement("select");
    this.IsoSelect.style.width = "100%";
    this.IsoSelect.options[this.IsoSelect.options.length] = new Option(
      i18n.Translate("Select an Option..."),
      "null",
      true,
      true
    );
    Drives.forEach((drive) => {
      var Op = new Option(drive.Name, drive.Id, false, false);
      this.IsoSelect.options[this.IsoSelect.options.length] = Op;
    });
    this.IsoFiles = Drives;
  };

  /* Handle incoming VM Screenshot
    Screenshot: Base64 Encoded image
  */
  this.HandleVmScreenshot = (Screenshot) => {
    if (Screenshot.startsWith('{"')) {
      // response is json
      var data = JSON.parse(Screenshot);
      if (data.err) {
        if (data.err === "VM is not PoweredOn") {
          // TODO
        } else if (data.err === "VM is not Deployed") {
          // TODO
        } else {
          console.log("Error on getting vm screenshot");
        }
      }
    } else {
      document.getElementById(
        "screenShot"
      ).src = `data:image/png;base64,${Screenshot}`;
    }
    if (this._getVmScreenshotTimeoutId !== 0) {
      clearTimeout(this._getVmScreenshotTimeoutId);
      this._getVmScreenshotTimeoutId = 0;
    }
    this._getVmScreenshotTimeoutId = setTimeout(
      this.GetVmScreenshot,
      RELOAD_SCREENSHOT_INTERVAL ? RELOAD_SCREENSHOT_INTERVAL : 5000
    );
  };

  /* Handle incoming Monitoring data
    Data: {
      OverallCpuUsage   Number
      MaxCpuUsage       Number
      GuestMemoryUsage  Number
      MaxMemoryUsage    Number
    }
  */
  this.HandleMonitoringData = (Data) => {
    var MonitoringData = JSON.parse(Data);

    UpdateGaugeBar(
      "cpu_chart",
      "CPU Usage",
      MonitoringData.OverallCpuUsage * 1024,
      MonitoringData.MaxCpuUsage * 1024,
      "Hz"
    );
    UpdateGaugeBar(
      "ram_chart",
      "Ram Usage",
      MonitoringData.GuestMemoryUsage * 1024,
      MonitoringData.MaxMemoryUsage * 1024,
      "B"
    );

    var chartId = ("" + MonitoringData.PerformanceType).split(":")[0];
    var name = "";
    var title = "";
    if (chartId.indexOf("cpu") !== -1) {
      name = "CPU Usage";
      title = "CPU Usage";
    } else if (chartId.indexOf("ram") !== -1) {
      name = "RAM Usage";
      title = "RAM Usage";
    }

    UpdateLineGraph(chartId, name, MonitoringData.Performance, title);

    if (this._getMonitoringDataTimeoutId !== 0) {
      clearTimeout(this._getMonitoringDataTimeoutId);
      this._getMonitoringDataTimeoutId = 0;
    }
    if (this._OpenedTab === "Monitoring") {
      this._getMonitoringDataTimeoutId = setTimeout(
        this.GetMonitoringData,
        RELOAD_MONITORING_INTERVAL ? RELOAD_MONITORING_INTERVAL : 5000
      );
    }
  };

  /* sending an object to the server
    Param: {
      Type  String  NotNull { "GetPanelDetails", "PowerOn", "PowerOff", "PowerReset", "GetCdDrives", "InsertISO", "EjectISO", "VmSuspend", "SetupOS", "Delete", "Deploy", "Troubleshoot" }
      Param AnyType
    }
  */
  this._sendObject = (Param) => {
    if (!this.conn) {
      return false;
    }
    if (typeof Param != "object") {
      return false;
    }
    this.conn.send(JSON.stringify(Param));
    return true;
  };

  /* sending a command to the server
    Param: {
      Type  String  NotNull { "GetPanelDetails", "PowerOn", "PowerOff", "PowerReset", "GetCdDrives", "InsertISO", "EjectISO", "VmSuspend", "SetupOS", "Delete", "Deploy", "Troubleshoot" }
      Param AnyType
    }
  */
  this._sendCommand = (command) => {
    return this._sendObject({
      Type: command,
    });
  };

  // send Reload signal to the server
  this._reloadDetails = () => {
    this._sendObject({
      Type: "GetPanelDetails",
    });
  };

  // send Get Scripts signal to the server
  this._reloadScripts = () => {
    this._sendObject({
      Type: "GetPanelScripts",
    });
  };

  /* Handle incoming command from the server
    Data: {
      type  String  NotNull { "SetPanelDetails", "SetStatus", "SetServiceSuspended", "SetOsPassword", "SetIpAddress", "SetActions", "Reload", "Message", "Action", "SetCdDrives" }
      data  String
    }
  */
  this._parseIncomingCommand = (Data) => {
    if (!Data || !Data.type) return;
    switch (Data.type) {
      case "SetPanelDetails":
        this.onPanelDetailsChanged(Data.data);
        break;
      case "SetPanelScripts":
        this.onPanelScriptsChanged(Data.data);
        break;
      case "ExecScriptResult":
        if (window.onExecScriptResult) {
          window.onExecScriptResult(Data.data); // on ./scripts.js
        }
        break;
      case "ExecScriptUpdate":
        if (window.onExecScriptUpdate) {
          window.onExecScriptUpdate(Data.data); // on ./scripts.js
        }
        break;
      case "SetStatus":
        this.onStatusChanged(Data.data);
        break;
      case "SetServiceSuspended":
        this.onServiceSuspendedChanged(Data.data === "true");
        break;
      case "SetOsPassword":
        this.onPasswordChanged(Data.data);
        break;
      case "SetIpAddress":
        this.onIpAddressChanged(Data.data);
        break;
      case "SetActions":
        var actionNames = Data.data.split(",");
        this.onActionsChanged(actionNames);
        break;
      case "Reload":
        this._reloadDetails();
        break;
      case "Message":
        this.HandleMessage(Data.data);
        break;
      case "Action":
        this.HandleAction(Data.data);
        break;
      case "SetCdDrives":
        this.HandleCdDrivesList(Data.data);
        break;
      case "SetPlanTemplates":
        this.HandlePlanTemplatesList(Data.data);
        break;
      case "SetVmScreenshot":
        this.HandleVmScreenshot(Data.data);
        break;
      case "SetMonitoringData":
        this.HandleMonitoringData(Data.data);
        break;
    }
  };

  this.onConnectionLost = (WebSocketOncloseEvent) => {
    ShowMessage(i18n.Translate("Connection Lost, Please try again..."));
    DEBUG && console.log("WebSocket Closed, Event:", WebSocketOncloseEvent);
  };

  this.onConnectionEstablished = (WebSocketOnopenEvent) => {
    document.getElementById("vmb_panel_msg").classList.add("hidden");
    this._reloadDetails();
    setInterval(
      this._reloadDetails,
      RELOAD_PANEL_INTERVAL ? RELOAD_PANEL_INTERVAL : 50000
    );
    this._reloadScripts();
    setInterval(
      this._reloadScripts,
      RELOAD_SCRIPTS_INTERVAL ? RELOAD_SCRIPTS_INTERVAL : 30000
    );
    if (window.ExecScript) {
      // set Exec Script func, on ./script.js
      window.ExecScript = (ScriptId, Params) => {
        var Req = {
          ScriptId: ScriptId,
          Params: Params
        };
        this._sendObject({
          Type: "ExecScript",
          Param: `${JSON.stringify(Req)}`,
        });
      }
      window.CancelScript = (ScriptExecId) => {
        this._sendObject({
          Type: "CancelScript",
          Param: `${ScriptExecId}`,
        });
      }
    }
  };

  this.Setup = () => {
    if (window["WebSocket"]) {
      ShowMessage(i18n.Translate("Connecting to Panel...", "info"));
      this.conn = new WebSocket(WebSocketAddress);
      this.conn.onclose = (evt) => {
        this.onConnectionLost(evt);
      };
      this.conn.onopen = (evt) => {
        this.onConnectionEstablished(evt);
      };
      this.conn.onmessage = (evt) => {
        try {
          var Incomings = JSON.parse(evt.data);
          Incomings.forEach((cmd) => {
            this._parseIncomingCommand(cmd);
          });
        } catch { }
      };
    } else {
      alert(
        "Sorry, your browser doesn't support websocket, please contact service provider"
      );
    }
  };

  /*  Misc  */
  this.GetVmScreenshot = () => {
    this._sendObject({
      Type: "GetVmScreenshot",
    });
  };
  this.GetMonitoringData = () => {
    var timespan = "RealTime";
    var timeSpanElem = document.getElementById(
      `${this._OpenedUsageType}TimeSpan`
    );
    if (
      timeSpanElem &&
      timeSpanElem.selectedOptions &&
      timeSpanElem.selectedOptions.length > 0 &&
      timeSpanElem.selectedOptions[0].value !== "null"
    ) {
      timespan = timeSpanElem.selectedOptions[0].value;
    }
    this._sendObject({
      Type: "GetMonitoringData",
      Param: `${this._OpenedUsageType}:${timespan}`,
    });
    if (this._getVmScreenshotTimeoutId !== 0) {
      this._getVmScreenshotTimeoutId = 0;
    }
  };
  this.OpenServerVnc = () => {
    if (this.Status !== "PoweredOn") {
      alert(i18n.Translate("Server is not ready or Powered On"));
      return;
    }
    window.open(window.location.href + "&console=novnc", "_blank");
  };
  this.OpenServerConsole = () => {
    if (this.Status !== "PoweredOn") {
      alert(i18n.Translate("Server is not ready or Powered On"));
      return;
    }
    window.open(window.location.href + "&console=wmks", "_blank");
  };
  this.OpenActionDetails = (ActionTitle) => {
    if (!this._actionInProgress && this._actionInProgress != -1) {
      return;
    }
    var content = document.createElement("div");
    return OpenModal(ActionTitle, content, "actionDetailsModal");
  };

  this.CloseActionDetails = () => {
    CloseModal("actionDetailsModal");
    if (this._actionInProgress) {
      this._isActionDetailsForcedToClosed = true;
    } else {
      this._isActionDetailsForcedToClosed = false;
      // TODO: Hide see current action details button
    }
  };
  this.OpenRemoteConnection = () => {
    var iframe = document.createElement("iframe");
    iframe.style.width = "100%";
    iframe.style.height = "100%";
    iframe.style.minHeight = "85vh";
    iframe.style.border = "none";
    iframe.style.marginTop = "-5px";
    iframe.src = window.location.href + "&console=wmks";
    var modal = OpenModal(
      i18n.Translate("Remote Connection"),
      iframe,
      "remoteConnectionModal"
    );
    modal.querySelector("#modalPaper").classList.add("full-width");
    modal.querySelector("#modalContent").style.paddingTop = "0px";
  };
  this.OpenAccessInformation = () => {
    var info = document.createElement("div");
    info.style.direction = "ltr";
    info.style.padding = "15px";
    info.innerHTML = `<p>IP Address: ${this.IpAddress}</p><p>Username: ${this.Username}</p><p>Password: ${this.Password}</p>`;
    OpenModal(i18n.Translate("Access Information"), info, "accessInfo");
  };

  // options is { title, yesBtnText, noBtnText }
  this.OpenConfirmBox = (msg, onYes, options) => {
    var content = document.createElement("div");
    {
      var Message = document.createElement("div");
      {
        Message.style.padding = "15px 25px";
        if (typeof msg === "string") {
          Message.innerHTML = msg;
        } else {
          Message.appendChild(msg);
        }
      }
      content.appendChild(Message);

      var buttons = document.createElement("div");
      {
        buttons.style.display = "flex";
        buttons.style.justifyContent = "center";
        buttons.style.paddingBottom = "10px";
        var yes = document.createElement("button");
        {
          yes.classList.add("btn");
          yes.style.margin = "5px 6px";
          yes.innerHTML =
            options && options.yesBtnText
              ? options.yesBtnText
              : i18n.Translate("Yes");
          yes.onclick = () => {
            this.CloseConfirmBox();
            onYes();
          };
        }
        buttons.appendChild(yes);

        var no = document.createElement("button");
        {
          no.classList.add("btn");
          no.style.margin = "5px 6px";
          no.innerHTML =
            options && options.noBtnText
              ? options.noBtnText
              : i18n.Translate("No");
          no.onclick = () => this.CloseConfirmBox();
        }
        buttons.appendChild(no);
      }
      content.appendChild(buttons);
    }
    OpenModal(
      options && options.title
        ? options.title
        : i18n.Translate("Are you sure?"),
      content,
      "confirmBox"
    );
  };
  this.CloseConfirmBox = () => {
    CloseModal("confirmBox");
  };
}
