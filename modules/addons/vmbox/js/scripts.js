var Vmbox_Scripts = null;

function Set_Vmbox_Scripts(scs) {
  if (!scs || (!scs.length && scs.length !== 0)) return;

  // check if logos or names are changed
  var changed = Vmbox_Scripts === null ? true : scs.length !== Vmbox_Scripts.length;
  if (!changed) {
    for (var i = 0; i < scs.length && !changed; i++) {
      if (scs[i].Name !== Vmbox_Scripts[i].Name || (scs[i].LogoBase64 && !Vmbox_Scripts[i].LogoBase64) || (!scs[i].LogoBase64 && Vmbox_Scripts[i].LogoBase64) || scs[i].LogoBase64.length !== Vmbox_Scripts[i].LogoBase64.length) {
        changed = true;
      }
      if (
        (scs[i].LastRun && !Vmbox_Scripts[i].LastRun) ||
        (!scs[i].LastRun && Vmbox_Scripts[i].LastRun) ||
        (
          (scs[i].LastRun && Vmbox_Scripts[i].LastRun) &&
          (
            (scs[i].LastRun.Done !== Vmbox_Scripts[i].LastRun.Done) ||
            (scs[i].LastRun.Failed !== Vmbox_Scripts[i].LastRun.Failed) ||
            (scs[i].LastRun.Canceled !== Vmbox_Scripts[i].LastRun.Canceled)
          )
        )) {
        changed = true;
      }
    }
  }


  // set the new scripts
  Vmbox_Scripts = scs.length ? scs : [];

  // ReBuild elements if changed
  if (changed) BuildScriptElements();
}

function BuildScriptElements() {
  var scriptList = document.getElementById("ScriptsList");
  if (scriptList) {
    scriptList.innerHTML = "";
    for (var i = 0; i < Vmbox_Scripts.length; i++) {
      var scriptDiv = CreateScriptElement(i);
      scriptList.appendChild(scriptDiv);
    }
    if (Vmbox_Scripts.length === 0) {
      document.getElementById("ScriptsTabButton").style.display = "none";
      scriptList.innerHTML = i18n.Translate("No Scripts available right now!");
    } else {
      document.getElementById("ScriptsTabButton").style.display = "unset";
    }
  } else {
    console.log("script list not found");
  }
}


function CreateScriptElement(index) {
  var ScriptDetails = Vmbox_Scripts[index];
  if (!ScriptDetails) return;

  var container = CreateElement("div", { id: `ScriptElem_${ScriptDetails.ScriptId}`, classList: ["script"], onclick: OpenScriptModal(index) });

  if (ScriptDetails.LastRun) {
    if (ScriptDetails.LastRun.Done) {
      if (ScriptDetails.LastRun.Failed) {
        container.classList.add("failed");
      } else if (ScriptDetails.LastRun.Canceled) {
        container.classList.add("canceled");
      } else {
        container.classList.add("done");
      }
    } else {
      container.classList.add("active")
    }
  }

  var imgSrc = ScriptDetails.LogoBase64;
  if (imgSrc.length > 10 && !imgSrc.startsWith("data:image/png;base64,")) { imgSrc = `data:image/png;base64,${imgSrc}` }
  var image = CreateElement("img", { src: imgSrc });
  container.appendChild(image);

  var title = CreateElement("div", { innerHTML: ScriptDetails.Name, classList: ["scriptTitle"] });
  container.appendChild(title);

  return container;
}

function OpenScriptModal(index) {
  return () => {
    var ScriptDetails = Vmbox_Scripts[index];
    if (!ScriptDetails) return;

    var defaultTabButton;
    var content = CreateElement("div", { classList: ["scriptModalContent"] });
    {
      var Desc = CreateElement("div", { classList: ["scriptDesc"], innerHTML: ScriptDetails.Desc })

      var tab = CreateElement("div", { classList: ["tab", "i18n"] });
      {
        var RunButton = CreateElement("button", { classList: ["tablinks", "i18n"], onclick: (e) => OpenScriptModalTab(content, "RunTab", e), innerHTML: "Run" })
        tab.appendChild(RunButton);

        var DetailsButton = CreateElement("button", { id: `ScriptDetailsBtn_${ScriptDetails.ScriptId}`, classList: ["tablinks", "i18n", ScriptDetails.LastRun ? "-" : "d-none"], onclick: (e) => { makeScriptModalWide(); e.target.classList.remove("d-none"); OpenScriptModalTab(content, "DetailsTab", e); }, innerHTML: "Details" })
        tab.appendChild(DetailsButton);

        var StdOutBtn = CreateElement("button", { id: `ScriptStdOutBtn_${ScriptDetails.ScriptId}`, classList: ["tablinks", "i18n", ScriptDetails.LastRun && ScriptDetails.LastRun.STDOUT !== "" ? "-" : "d-none"], onclick: (e) => { makeScriptModalWide(); e.target.classList.remove("d-none"); OpenScriptModalTab(content, "StdOutTab", e); }, innerHTML: "Output" })
        tab.appendChild(StdOutBtn);
        var StdErrBtn = CreateElement("button", { id: `ScriptStdErrBtn_${ScriptDetails.ScriptId}`, classList: ["tablinks", "i18n", ScriptDetails.LastRun && ScriptDetails.LastRun.STDERR !== "" ? "-" : "d-none"], onclick: (e) => { makeScriptModalWide(); e.target.classList.remove("d-none"); OpenScriptModalTab(content, "StdErrTab", e); }, innerHTML: "Error" })
        tab.appendChild(StdErrBtn);

        defaultTabButton = ScriptDetails.LastRun ? DetailsButton : RunButton;
      }

      var RunTabContent = CreateElement("div", { id: "RunTab", classList: ["tabcontent"] });
      {
        var RunParamContent = CreateRunTabParams(ScriptDetails);
        RunTabContent.appendChild(RunParamContent);
      }

      var DetailsTabContent = CreateElement("div", { id: "DetailsTab", classList: ["tabcontent"] });
      {
        var DetailsContent = ScriptDetails.Name;
        if (ScriptDetails.LastRun) {// load previous content
          if (!ScriptDetails.LastRun.detailsContent) {
            generateCallbacksString(index);
          }
          DetailsContent = ScriptDetails.LastRun.detailsContent;
        }
        var DetailsContent = CreateElement("textarea", { id: `detailsContent_${ScriptDetails.ScriptId}`, classList: ["textContent"], innerHTML: DetailsContent });
        DetailsTabContent.appendChild(DetailsContent);
        var cancelDiv = CreateElement("div", { id: `CancelScript_${ScriptDetails.ScriptId}`, classList: ["d-flex", "justify-content-end"] });
        var cancelBtn = CreateElement("button", { classList: ["borderedBtn", "i18n"], onclick: () => { if (ScriptDetails.LastRun) { CancelScript(ScriptDetails.LastRun.ExecId) } }, innerHTML: "Cancel Execution!" });
        cancelBtn.style.padding = "10px";
        cancelDiv.appendChild(cancelBtn);
        DetailsTabContent.appendChild(cancelDiv);
        if (ScriptDetails.LastRun && !ScriptDetails.LastRun.Done) {
          cancelDiv.classList.remove("d-none");
        } else {
          cancelDiv.classList.add("d-none");
        }
      }

      var StdOutTabContent = CreateElement("div", { id: "StdOutTab", classList: ["tabcontent"] });
      {
        var StdOutContent = ScriptDetails.Name;
        if (ScriptDetails.LastRun) {// load previous content
          StdOutContent = ScriptDetails.LastRun.STDOUT;
        }
        var StdOutContent = CreateElement("textarea", { id: `stdOutContent_${ScriptDetails.ScriptId}`, classList: ["textContent"], innerHTML: StdOutContent });
        StdOutTabContent.appendChild(StdOutContent);
      }

      var StdErrTabContent = CreateElement("div", { id: "StdErrTab", classList: ["tabcontent"] });
      {
        var StdErrContent = ScriptDetails.Name;
        if (ScriptDetails.LastRun) {// load previous content
          if (!ScriptDetails.LastRun.detailsContent) {
            StdErrContent = ScriptDetails.LastRun.STDERR;
          }
        }
        var StdErrContent = CreateElement("textarea", { id: `stdErrContent_${ScriptDetails.ScriptId}`, classList: ["textContent"], innerHTML: StdErrContent });
        StdErrTabContent.appendChild(StdErrContent);
      }

      content.appendChild(Desc);
      content.appendChild(tab);
      content.appendChild(RunTabContent);
      content.appendChild(DetailsTabContent);
      content.appendChild(StdOutTabContent);
      content.appendChild(StdErrTabContent);
    }
    OpenModal(ScriptDetails.Name, content, "scriptModal", ScriptDetails.LogoBase64);
    if (window.i18n) window.i18n.TranslateAllDocument();
    if (defaultTabButton) defaultTabButton.click()
  };
}

function OpenScriptModalTab(parent, tabName, evt) {
  var i, tabcontent, tablinks;
  tabcontent = parent.querySelectorAll(".tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = parent.querySelectorAll(".tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  parent.querySelector(`#${tabName}`).style.display = "block";
  if (evt) evt.currentTarget.className += " active";
}


function CreateRunTabParams(ScDetails) {
  var Params = ScDetails.Params;
  var content = CreateElement("div", { id: `ParamContent_${ScDetails.ScriptId}`, classList: ["paramContent"] });

  for (var i = 0; i < Params.length; i++) {
    var inpContainer = CreateElement("div", { classList: ["param"] });
    var lbl = CreateElement("label", { for: `ParamInp_${Params[i].Name}`, innerHTML: `${Params[i].Title}` })
    var inp;
    switch (Params[i].Type) {
      case "string":
        inp = CreateElement("input", { id: `ParamInp_${Params[i].Name}`, type: "text", defaultValue: Params[i].Default, placeholder: `${Params[i].Desc}` })
        break;
      case "number":
        inp = CreateElement("input", { id: `ParamInp_${Params[i].Name}`, type: "number", defaultValue: Params[i].Default, placeholder: `${Params[i].Desc}` })
        break;
      case "pass":
        lbl.innerHTML = lbl.innerHTML + ((Params[i].Desc && Params[i].Desc !== "") ? ` (${Params[i].Desc})` : "");
        inp = CreateElement("input", { id: `ParamInp_${Params[i].Name}`, type: "password", defaultValue: Params[i].Default });
        break;
      case "passWithConfirm":
        lbl.innerHTML = lbl.innerHTML + ((Params[i].Desc && Params[i].Desc !== "") ? ` (${Params[i].Desc})` : "");
        inp = CreateElement("div", {});
        var firstPass = CreateElement("input", { id: `ParamInp_${Params[i].Name}`, type: "password" });
        var secondLbl = CreateElement("label", { for: `ParamInp_${Params[i].Name}_2`, innerHTML: `Repeat Password` })
        var secondPass = CreateElement("input", { id: `ParamInp_${Params[i].Name}_2`, type: "password" });
        inp.appendChild(firstPass);
        inp.appendChild(secondLbl);
        inp.appendChild(secondPass);
        break;
      case "bool":
        inp = CreateElement("div", { innerHTML: `<span>${Params[i].Desc}</span>`, classList: ["d-flex", "justify-content-between"] });
        inp.style.paddingTop = "5px";
        var swLbl = CreateElement("label", { classList: ["paramSwitch"] });
        var sw = CreateElement("input", { id: `ParamInp_${Params[i].Name}`, type: "checkbox", defaultChecked: Params[i].Default });
        swLbl.appendChild(sw);
        swLbl.appendChild(CreateElement("div", {}));
        inp.appendChild(swLbl);
        break;
      case "select":
        inp = CreateElement("label", { for: `ParamInp_${Params[i].Name}`, classList: ["paramSelect"] })
        var slct = CreateElement("select", { id: `ParamInp_${Params[i].Name}`, defaultValue: Params[i].Default, innerHTML: `${Params[i].Desc && Params[i].Desc !== "" ? `<optgroup><option disabled value="_description">${Params[i].Desc}</option></optgroup>` : ""}${Params[i].SelectOptions}` });
        inp.appendChild(slct);
        inp.appendChild(CreateElement("svg", { innerHTML: `<use xlink:href="#select-arrow-down"></use>` }));
        inp.appendChild(CreateElement("svg", {
          classList: ["vmb_sprites"], innerHTML: `<symbol id="select-arrow-down" viewbox="0 0 10 6"><polyline points="1 1 5 5 9 1"></polyline></symbol>`
        }));
        break;
      case "file":
        inp = CreateElement("div", {});
        var inpF = CreateElement("input", { id: `ParamInp_${Params[i].Name}`, type: "file", placeholder: `${Params[i].Desc}` })
        if (Params[i].FileType && Params[i].FileType !== "") {
          inpF.accept = `${Params[i].FileType}`;
        }
        inpF.style.fontSize = ".8rem";
        inpF.style.padding = "1px";
        inpF.onchange = ((p) => (e) => {
          window.paramFiles = {};
          var fFile = e.target.files[0];
          if (p.MaxFileSize && p.MaxFileSize > 0) { // check file size
            var MaxFileSize = p.MaxFileSize * 1024; // KB
            if (p.MaxFileSizeUnit === "MB") {
              MaxFileSize = MaxFileSize * 1024;
            }
            if (fFile.size > MaxFileSize) { alert(`Selected file must be smaller than ${p.MaxFileSize} ${p.MaxFileSizeUnit}`); return; }
          }
          var fr = new FileReader();
          fr.onloadend = () => {
            var Based64_File = ConvertArrayBufferToBase64(fr.result);
            window.paramFiles[p.Name] = Based64_File;
          };
          fr.readAsArrayBuffer(fFile);
        })(Params[i]);
        inp.appendChild(inpF);
        inp.appendChild(CreateElement("span", { innerHTML: `${Params[i].Desc}` }))
        break;
    }
    inpContainer.appendChild(lbl);
    inpContainer.appendChild(inp);
    content.appendChild(inpContainer);
    content.appendChild(CreateElement("span", { classList: ["sep"] }));
  }

  var btnDiv = CreateElement("div", { classList: ["d-flex", "justify-content-center"] });
  var RunBtn = CreateElement("button", { classList: ["borderedBtn", "w-100"], innerHTML: `Run`, onclick: e => RunScript(ScDetails, e) })
  RunBtn.style.maxWidth = "150px";
  RunBtn.style.margin = "auto";

  btnDiv.appendChild(RunBtn);
  content.appendChild(btnDiv);

  return content;
}


function RunScript(ScDetails, evt) {
  var pCont = document.querySelector(`#ParamContent_${ScDetails.ScriptId}`);
  if (!pCont) return
  var Params = ScDetails.Params;
  var ParamsResult = {};
  if (Params) {
    for (var i = 0; i < Params.length; i++) {
      var p = Params[i];
      var inp = pCont.querySelector(`#ParamInp_${p.Name}`);
      if (!inp && p.Required) { alert(`${p.Title} is required`); return; }
      switch (p.Type) {
        case "string":
          if (inp.value === "" && p.Required) { alert(`${p.Title} is required`); return; }
          ParamsResult[p.Name] = `${inp.value}`;
          break;
        case "number":
          if (inp.value === "" && p.Required) { alert(`${p.Title} is required`); return; }
          var N = Number(inp.value);
          if (isNaN(N)) { alert(`${p.Title} must be a number`); return; }
          ParamsResult[p.Name] = `${inp.value}`;
          break;
        case "pass":
          if (inp.value === "" && p.Required) { alert(`${p.Title} is required`); return; }
          ParamsResult[p.Name] = `${inp.value}`;
          break;
        case "passWithConfirm":
          var inp2 = pCont.querySelector(`#ParamInp_${p.Name}_2`);
          if (inp.value === "" && p.Required) { alert(`${p.Title} is required`); return; }
          if (!inp2 || inp2.value !== inp.value) { alert(`Repeat Password is not correct`); return; }
          ParamsResult[p.Name] = `${inp.value}`;
          break;
        case "bool":
          ParamsResult[p.Name] = `${inp.checked ? "true" : "false"}`;
          break;
        case "select":
          if (inp.value === "" && p.Required) { alert(`${p.Title} is required`); return; }
          ParamsResult[p.Name] = `${inp.value}`;
          break;
        case "file":
          if (!inp.files || inp.files.length < 1) { alert(`${p.Title} is required`); return; }
          if (!window.paramFiles || !window.paramFiles[p.Name]) { alert(`${p.Title} is required`); return; }
          ParamsResult[p.Name] = window.paramFiles[p.Name];
          break;
      }
    }
  }
  ExecScript(ScDetails.ScriptId, ParamsResult);
}

function ExecScript(ScriptId, Params) {
  // this function will be changed by VMBOX Setup
  // this is going to send ScriptId and Params back to server
}

function CancelScript(ScriptExecId) {
  // this function will be changed by VMBOX Setup
  // this is going to send ScriptId and Params back to server
}

function onExecScriptResult(Result) {
  Result = JSON.parse(Result);

  if (Result.Res !== "success") {
    console.log(Result.Data);
    return
  }

  var detailsBtn = document.querySelector(`#ScriptDetailsBtn_${Result.ScriptId}`);
  if (detailsBtn && detailsBtn.click) {
    // click it
    detailsBtn.click();
  }

  var stdoutBtn = document.querySelector(`#ScriptStdOutBtn_${Result.ScriptId}`);
  if (stdoutBtn) {
    stdoutBtn.classList.add("d-none");
  }
  var stderrBtn = document.querySelector(`#ScriptStdErrBtn_${Result.ScriptId}`);
  if (stderrBtn) {
    stderrBtn.classList.add("d-none");
  }
}

function onExecScriptUpdate(LastRun) {
  if (Vmbox_Scripts) {
    for (var i = 0; i < Vmbox_Scripts.length; i++) {
      if (Vmbox_Scripts[i].ScriptId === LastRun.ScriptId) {
        Vmbox_Scripts[i].LastRun = LastRun;
        UpdateScriptExecDetails(i);
        return;
      }
    }
  }
}

function UpdateScriptExecDetails(ScriptIndexAtVmbox_Scripts) {
  var sc = Vmbox_Scripts[ScriptIndexAtVmbox_Scripts];
  if (!sc) return;

  // set Script Element Border type
  var scElem = document.querySelector(`#ScriptElem_${sc.ScriptId}`);
  if (!scElem) return;

  var cancelDiv = document.querySelector(`#CancelScript_${sc.ScriptId}`);
  if (sc.LastRun.Done) {
    scElem.classList.remove("active");
    scElem.classList.add(sc.LastRun.Failed ? "failed" : sc.LastRun.Canceled ? "canceled" : "done");
    if (cancelDiv) cancelDiv.classList.add("d-none");
  } else {
    scElem.classList.add("active");
    scElem.classList.remove("done");
    scElem.classList.remove("failed");
    scElem.classList.remove("canceled");
    if (cancelDiv) cancelDiv.classList.remove("d-none");
  }

  var cbs = generateCallbacksString(ScriptIndexAtVmbox_Scripts);

  var DetailsContent = document.querySelector(`#detailsContent_${sc.ScriptId}`);
  if (!DetailsContent) return;

  DetailsContent.innerHTML = cbs;

  if (sc.LastRun.STDOUT !== "") {
    var stdoutBtn = document.querySelector(`#ScriptStdOutBtn_${sc.ScriptId}`);
    if (stdoutBtn) {
      stdoutBtn.classList.remove("d-none");
    }
    var stdoutContent = document.querySelector(`#stdOutContent_${sc.ScriptId}`);
    if (stdoutContent) {
      stdoutContent.innerHTML = sc.LastRun.STDOUT;
    }
  }

  if (sc.LastRun.STDERR !== "") {
    var stderrBtn = document.querySelector(`#ScriptStdErrBtn_${sc.ScriptId}`);
    if (stderrBtn) {
      stderrBtn.classList.remove("d-none");
    }
    var stderrContent = document.querySelector(`#stdErrContent_${sc.ScriptId}`);
    if (stderrContent) {
      stderrContent.innerHTML = sc.LastRun.STDERR;
    }
  }

  makeScriptModalWide();
}

function generateCallbacksString(ScriptIndexAtVmbox_Scripts) {
  var sc = Vmbox_Scripts[ScriptIndexAtVmbox_Scripts];
  if (!sc) return;

  var LastRun = sc.LastRun;
  if (!LastRun) return;

  var OsHostname = "server";
  if (window.vmbox) {
    OsHostname = window.vmbox.Hostname;
  }

  var cbs = `Script Started at ${UnixToDateString(
    LastRun.StartTime
  )}\n`;
  if (LastRun && LastRun.Callbacks && LastRun.Callbacks.length) {
    LastRun.Callbacks.forEach((cb) => {
      cbs =
        cbs +
        `root@${OsHostname}:/# ${cb.Type} ${cb.Content !== "" ? `(${cb.Content})` : ""
        }\n`;
    });
  }

  sc.LastRun.detailsContent = cbs;

  return cbs;
}

function makeScriptModalWide() {
  // make modal full width if open
  var mdl = document.querySelector("#scriptModal");
  if (mdl) {
    var ppr = mdl.querySelector("#modalPaper");
    if (ppr) {
      ppr.classList.add("full-width");
    }
  }
}
