
function EnableElementById(Id) {
  document.getElementById(Id).classList.remove("disabled");
}
function DisableElementById(Id) {
  document.getElementById(Id).classList.add("disabled");
}

function ShowElementById(Id) {
  document.getElementById(Id).classList.remove("hidden");
}
function HideElementById(Id) {
  document.getElementById(Id).classList.add("hidden");
}

function openTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
  if (window.vmbox) {
    window.vmbox._OpenedTab = tabName;
    if (tabName === "Monitoring") {
      window.vmbox.GetMonitoringData();
    } else {
      if (window.vmbox._getMonitoringDataTimeoutId !== 0) {
        clearTimeout(window.vmbox._getMonitoringDataTimeoutId);
        window.vmbox._getMonitoringDataTimeoutId = 0;
      }
    }
  }
}

function selectUsageType(evt, usageType) {
  var i, usageGraphContainer, usage;
  usageGraphContainer = document.getElementsByClassName("usageGraphContainer");
  for (i = 0; i < usageGraphContainer.length; i++) {
    usageGraphContainer[i].style.display = "none";
  }
  usage = document.getElementsByClassName("graphTitle");
  for (i = 0; i < usage.length; i++) {
    usage[i].className = usage[i].className.replace(" selected", "");
  }
  document.getElementById(usageType).style.display = "block";
  evt.currentTarget.className += " selected";
  if (window.vmbox) {
    window.vmbox._OpenedUsageType = usageType;
    window.vmbox.GetMonitoringData();
  }
}

function timeSpanChanged(e) {
  if (vmbox) {
    e.target.parentElement.click();
    vmbox.GetMonitoringData();
  }
}

function ShowMessage(Content, Type) {
  document.getElementById("vmb_panel_msg").classList.remove("hidden");
  document.getElementById("vmb_panel_msg_content").innerHTML = Content;
  document.getElementById("vmb_panel_msg_content").className = Type;
}

function OpenModal(Title, Content, modalSelector, LogoBase64) {
  var modal;
  if (modalSelector && modalSelector !== "vmbModal") {
    var modalAlreadyExists = document.querySelector(`#${modalSelector}`);
    if (modalAlreadyExists) {
      modal = modalAlreadyExists;
    } else {
      // create new modal
      var newModal = document.createElement("div");
      newModal.id = `${modalSelector}`;
      newModal.classList.add("vmbModal");
      var newInnerHTML = "" + _modalTemplate;
      while (newInnerHTML.indexOf("('vmbModal')") !== -1) {
        newInnerHTML = newInnerHTML.replace(
          "('vmbModal')",
          `('${modalSelector}')`
        );
      }
      newModal.innerHTML = newInnerHTML;
      document.getElementById("panelContainer").appendChild(newModal);
      modal = newModal;
    }
  } else {
    modal = document.querySelector(
      `#${modalSelector ? modalSelector : "vmbModal"}`
    );
  }
  var modalPaper = modal.querySelector("#modalPaper");
  if (modal.className.indexOf("shown") === -1) {
    // modal is not yet shown, display the modal
    modal.classList.add("shown");
    modalPaper.classList.add("shown");
    if (
      i18n &&
      i18n.DefaultLanguage &&
      i18n.Languages[i18n.DefaultLanguage] &&
      i18n.Languages[i18n.DefaultLanguage]._RTL
    ) {
      modalPaper.style.direction = "rtl";
    }
  }
  modalPaper.querySelector("#modalName").innerHTML = Title;
  modalPaper.querySelector("#modalContent").innerHTML = "";
  modalPaper.querySelector("#modalContent").appendChild(Content);
  if (LogoBase64) {
    modalPaper.querySelector("#modalLogo").src = `data:image/png;base64,${LogoBase64}`;
    modalPaper.querySelector("#modalLogo").classList.remove("d-none");
  } else {
    modalPaper.querySelector("#modalLogo").classList.add("d-none");
  }
  return modal;
}

function CloseModal(modalSelector) {
  var modal = document.querySelector(
    `#${modalSelector ? modalSelector : "vmbModal"}`
  );
  if (modal) {
    modal.classList.remove("shown");
    var modalPaper = modal.querySelector("#modalPaper");
    modalPaper.classList.remove("shown");
    if (modalSelector && modalSelector !== "vmbModal") {
      // delete newly created modal
      var newModal = document.getElementById(`${modalSelector}`);
      if (newModal) {
        document.getElementById("panelContainer").removeChild(newModal);
      }
    }
  }
}

function toggleScreenshotFullscreen() {
  var imgElement = document.getElementById("screenShot");
  if (imgElement) {
    if (imgElement.className.indexOf("viewFullScreen") !== -1) {
      imgElement.classList.remove("viewFullScreen");
    } else {
      imgElement.classList.add("viewFullScreen");
    }
  }
}

function getGaugeBarOptions(ChartId, name, value, from, suffix) {
  var format = (val) => {
    val = parseFloat(val);
    if (val > 1024) {
      val = val / 1024;
      if (val > 1024) {
        return (val / 1024).toFixed(2) + " G" + suffix;
      }
      return val.toFixed(2) + " M" + suffix;
    }
    return val.toFixed(2) + " K" + suffix;
  };
  var formatted = format(value) + " / " + format(from);
  return (options = {
    series: [(value / from) * 100],
    chart: {
      height: 280,
      type: "radialBar",
    },
    plotOptions: {
      radialBar: {
        startAngle: -135,
        endAngle: 225,
        hollow: {
          margin: 0,
          size: "70%",
          background: "#fff",
          image: undefined,
          imageOffsetX: 0,
          imageOffsetY: 0,
          position: "front",
          dropShadow: {
            enabled: true,
            top: 3,
            left: 0,
            blur: 4,
            opacity: 0.24,
          },
        },
        track: {
          background: "#fff",
          strokeWidth: "67%",
          margin: 0, // margin is in pixels
          dropShadow: {
            enabled: true,
            top: -3,
            left: 0,
            blur: 4,
            opacity: 0.35,
          },
        },

        dataLabels: {
          show: true,
          name: {
            offsetY: -10,
            show: true,
            color: "#888",
            fontSize: "17px",
          },
          value: {
            formatter: function (val) {
              return formatted;
            },
            color: "#111",
            fontSize: "15px",
            offsetY: 0,
            show: true,
          },
        },
      },
    },
    fill: {
      type: "gradient",
      gradient: {
        shade: "dark",
        type: "horizontal",
        shadeIntensity: 0.5,
        gradientToColors: ["#ABE5A1"],
        inverseColors: true,
        opacityFrom: 1,
        opacityTo: 1,
        stops: [0, 100],
      },
    },
    stroke: {
      lineCap: "round",
    },
    labels: [name],
  });
}

function CreateGaugeBar(ChartId, name, value, from, suffix) {
  if (!Charts[ChartId]) {
    Charts[ChartId] = new ApexCharts(
      document.querySelector(`#${ChartId}`),
      getGaugeBarOptions(ChartId, name, value, from, suffix)
    );
  }
  Charts[ChartId].render();
}

function UpdateGaugeBar(ChartId, name, value, from, suffix) {
  if (Charts[ChartId]) {
    Charts[ChartId].updateOptions(
      getGaugeBarOptions(ChartId, name, value, from, suffix)
    );
  } else {
    CreateGaugeBar(ChartId, name, value, from, suffix);
  }
}

function getLineGraphOptions(ChartId, name, data, title) {
  return (options = {
    series: [
      {
        name: name,
        data: data ? data : [10, 10, 10, 10, 10],
      },
    ],
    chart: {
      height: 350,
      type: "line",

      animations: {
        enabled: true,
        easing: "linear",
        dynamicAnimation: {
          speed: 500,
        },
      },
      zoom: {
        enabled: false,
      },
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      show: true,
      curve: "straight",
      curve: "smooth",
      width: 3,
    },
    title: {
      text: title ? title : name,
      align: "left",
    },
    grid: {
      row: {
        colors: ["#f3f3f3", "transparent"], // takes an array which will be repeated on columns
        opacity: 0.5,
      },
    },
    tooltip: {
      x: {
        format: "dd/MM/yy HH:mm:ss",
      },
      y: {
        formatter: (val) => `${val}%`,
      },
    },
    xaxis: {
      type: "datetime",
    },
    yaxis: {
      labels: {
        formatter: (val) => `${parseFloat(val).toFixed(2)}%`,
      },
    },
  });
}

function CreateLineGraph(ChartId, name, data, title) {
  document.querySelector(`#${ChartId}`).innerHTML = "";
  Charts[ChartId] = new ApexCharts(
    document.querySelector(`#${ChartId}`),
    getLineGraphOptions(ChartId, name, data, title)
  );
  try {
    Charts[ChartId].render();
  } catch (e) { }
}

function UpdateLineGraph(ChartId, name, data, title) {
  if (Charts[ChartId]) {
    Charts[ChartId].updateOptions(
      getLineGraphOptions(ChartId, name, data, title)
    );
  } else {
    CreateLineGraph(ChartId, name, data, title);
  }
}


function CreateElement(Type, { id, className, classList, onclick, src, innerHTML, value, defaultValue, defaultChecked, placeholder, For, type }) {
  var elem = document.createElement(Type);
  if (id) {
    elem.id = id;
  }
  if (className) {
    elem.className = className;
  }
  if (classList && classList.length) {
    classList.forEach(c => {
      elem.classList.add(c);
    });
  }
  if (onclick) {
    elem.onclick = onclick;
  }
  if (src) {
    elem.src = src;
  }
  if (innerHTML) {
    elem.innerHTML = innerHTML;
  }
  if (value) {
    elem.value = value;
  }
  if (defaultValue) {
    elem.defaultValue = defaultValue;
  }
  if (defaultChecked) {
    elem.defaultChecked = defaultChecked;
  }
  if (placeholder) {
    elem.placeholder = placeholder;
  }
  if (For) {
    elem.for = For;
  }
  if (type) {
    elem.type = type;
  }
  return elem;
}

function UnixToDateString(UNIX_timestamp, Options) {
  var OnlyDate;
  if (Options) {
    OnlyDate = Options.OnlyDate;
  }
  var a = new Date(UNIX_timestamp * 1000);
  var months = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec",
  ];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes();
  var sec = a.getSeconds();
  var time = date + " " + month + " " + year;
  if (!OnlyDate) {
    time +=
      " " +
      (hour < 10 ? "0" + hour : hour) +
      ":" +
      (min < 10 ? "0" + min : min) +
      ":" +
      (sec < 10 ? "0" + sec : sec);
  }
  return time;
}

function ConvertArrayBufferToBase64(buffer) {
  var binary = "";
  var bytes = new Uint8Array(buffer);
  var len = bytes.byteLength;
  for (var i = 0; i < len; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return window.btoa(binary);
}