let ws = new WebSocket("ws://localhost:8000");
let chatBox = document.querySelectorAll("#chat-box")[0];
let userHandle = "";
console.log(ws);

ws.onopen = function (e) {
  let openMessage = document.createElement("div");
  openMessage.setAttribute("class", "openMessage");
  openMessage.appendChild(document.createTextNode("You are now connected!"));
  chatBox.appendChild(openMessage);
  document.querySelectorAll("#user")[0].removeAttribute("disabled");
  document.querySelectorAll("#message")[0].removeAttribute("disabled");
};

ws.onclose = function (e) {
  let closingMessage = document.createElement("div");
  closingMessage.setAttribute("class", "closingMessage");
  closingMessage.appendChild(
    document.createTextNode("The connection has been closed!")
  );
  chatBox.appendChild(closingMessage);
};

ws.onerror = function (e) {
  let errorMessage = document.createElement("div");
  errorMessage.setAttribute("class", "errorMessage");
  errorMessage.appendChild(document.createTextNode("An error has occurred!"));
  chatBox.appendChild(errorMessage);
};

ws.onmessage = function (e) {
  let data = JSON.parse(e.data);

  if (data != null) {
    let message = document.createElement("div");
    message.setAttribute("class", data.messageType);

    if (data.messageType == "receivedMessage") {
      if (data.message != null) {
        if (chatBox.lastChild.className == "receivedMessage") {
          message.appendChild(document.createTextNode(data.message));
        } else {
          message.appendChild(
            document.createTextNode(data.username + ": " + data.message)
          );
        }

        chatBox.appendChild(message);
        message.scrollIntoView({ behavior: "smooth" });
      }
    } else if (
      data.messageType == "typingNotification" &&
      chatBox.lastChild.className != "typingNotification"
    ) {
      document.querySelectorAll("#typingNotificationBox")[0].textContent = "";
      message.appendChild(
        document.createTextNode(data.username + " is typing...")
      );
      document
        .querySelectorAll("#typingNotificationBox")[0]
        .appendChild(message);
      setTimeout(function () {
        message.remove();
      }, 500);
    } else if (data.messageType == "activeConnections") {
      console.log(data.message);
    }
  }
};

document.querySelectorAll("#message")[0].addEventListener(
  "keyup",
  function (e) {
    let messageObj = {
      username: userHandle == "" ? "Someone" : userHandle,
      message: null,
      messageType: "typingNotification",
    };

    ws.send(JSON.stringify(messageObj));
  },
  false
);

document.addEventListener(
  "submit",
  function (e) {
    e.preventDefault();
    userHandle = document.querySelectorAll("#user")[0].value;
    document.querySelectorAll("#user")[0].style = "display: none";
    let messageData = document.querySelectorAll("#message")[0].value;
    let messageDiv = document.createElement("div");
    messageDiv.setAttribute("class", "sentMessage");

    if (chatBox.lastChild.className == "sentMessage") {
      messageDiv.appendChild(document.createTextNode(messageData));
    } else {
      messageDiv.appendChild(
        document.createTextNode(userHandle + ": " + messageData)
      );
    }

    chatBox.appendChild(messageDiv);
    messageDiv.scrollIntoView({ behavior: "smooth" });

    document.querySelectorAll("#message")[0].value = "";

    let messageObj = {
      username: userHandle,
      message: messageData,
      messageType: "receivedMessage",
    };

    ws.send(JSON.stringify(messageObj));
  },
  false
);
