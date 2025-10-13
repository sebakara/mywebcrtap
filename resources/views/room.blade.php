<!DOCTYPE html>
<html>
<head>
    <title>WebRTC Room {{ $roomId }}</title>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>

    <!-- <header> -->
    <div class="room-info">
        <h2>Room: {{ $roomId }}</h2>
    </div>

    <div class="join-container">
        <input type="text" id="usernameInput" placeholder="Enter your name" />
        <button onclick="joinRoom()" id="joinBtn">Join Room</button>
    </div>
<!-- </header> -->

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .room-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        header h2 {
            margin: 0;
            color: #333;
        }

        header button {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        header button:hover {
            background: #0056b3;
        }

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .video-wrapper {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            background: #000;
        }

        video {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }

        .local-video {
            border: 3px solid #007bff;
        }

        .username {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0,0,0,0.5);
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        video:hover {
            transform: scale(1.05);
            transition: transform 0.2s;
        }
        .join-container {
            display: flex;
            gap: 10px;
        }

        .join-container input {
            padding: 8px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
<div class="room-container">
    <header>
        <h2>Room: {{ $roomId }}</h2>
        <button onclick="joinRoom()" id="joinBtn">Join Room</button>
    </header>

    <div id="videos" class="videos-grid">
        <div class="video-wrapper">
            <span class="username">You</span>
            <video id="localVideo" autoplay playsinline muted class="local-video"></video>
        </div>
    </div>
</div>

<script>
    let username = "";
    let roomId = "{{ $roomId }}";
    let userId = Math.floor(Math.random() * 100000); // temporary random ID
    let peers = {}; // RTCPeerConnections per user
    let localStream;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: "{{ config('broadcasting.connections.pusher.key') }}",
        wsHost: window.location.hostname,
        wsPort: 6001,
        forceTLS: false,
        disableStats: true,
    });

    // Listen for signals in this room
    window.Echo.channel("webrtc-room-" + roomId)
        .listen("WebRTCSignaling", async (e) => {
            if (e.data.from === userId) return; // ignore own messages
            handleSignal(e.data);
        });

async function joinRoom() {
    let nameInput = document.getElementById("usernameInput").value.trim();
    if (!nameInput) {
        alert("Please enter your name before joining!");
        return;
    }
    username = nameInput;

    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    document.getElementById("localVideo").srcObject = localStream;

    // Add username label to local video
    document.querySelector("#videos .username").innerText = username;

    // announce to others
    sendSignal({ type: "join", from: userId, username: username });
    document.getElementById("joinBtn").disabled = true;
    document.getElementById("usernameInput").disabled = true;
}

    async function createPeerConnection(remoteId) {
        let pc = new RTCPeerConnection();

        localStream.getTracks().forEach(track => pc.addTrack(track, localStream));

        pc.ontrack = (event) => {
            let videoWrapper = document.getElementById("wrapper-" + remoteId);
            if (!videoWrapper) {
                videoWrapper = document.createElement("div");
                videoWrapper.className = "video-wrapper";
                videoWrapper.id = "wrapper-" + remoteId;

                let label = document.createElement("span");
                label.className = "username";
                label.innerText = "User " + remoteId;
                videoWrapper.appendChild(label);

                let videoEl = document.createElement("video");
                videoEl.id = "video-" + remoteId;
                videoEl.autoplay = true;
                videoEl.playsInline = true;
                videoWrapper.appendChild(videoEl);

                document.getElementById("videos").appendChild(videoWrapper);
            }
            document.getElementById("video-" + remoteId).srcObject = event.streams[0];
        };

        pc.onicecandidate = (event) => {
            if (event.candidate) {
                sendSignal({ type: "candidate", candidate: event.candidate, from: userId, to: remoteId });
            }
        };

        peers[remoteId] = pc;
        return pc;
    }

    async function handleSignal(data) {
        let remoteId = data.from;

        if (data.type === "join") {
            // Someone joined → create offer for them
            let pc = await createPeerConnection(remoteId);
            let offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            sendSignal({ type: "offer", offer: offer, from: userId, to: remoteId });
        }
        else if (data.type === "offer" && data.to === userId) {
            let pc = await createPeerConnection(remoteId);
            await pc.setRemoteDescription(new RTCSessionDescription(data.offer));
            let answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            sendSignal({ type: "answer", answer: answer, from: userId, to: remoteId });
        }
        else if (data.type === "answer" && data.to === userId) {
            let pc = peers[remoteId];
            await pc.setRemoteDescription(new RTCSessionDescription(data.answer));
        }
        else if (data.type === "candidate" && data.to === userId) {
            let pc = peers[remoteId];
            if (pc) {
                await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
            }
        }

        pc.ontrack = (event) => {
    let videoWrapper = document.getElementById("wrapper-" + remoteId);
    if (!videoWrapper) {
        videoWrapper = document.createElement("div");
        videoWrapper.className = "video-wrapper";
        videoWrapper.id = "wrapper-" + remoteId;

        let label = document.createElement("span");
        label.className = "username";
        label.innerText = data.username || "User " + remoteId; // use name if provided
        videoWrapper.appendChild(label);

        let videoEl = document.createElement("video");
        videoEl.id = "video-" + remoteId;
        videoEl.autoplay = true;
        videoEl.playsInline = true;
        videoWrapper.appendChild(videoEl);

        document.getElementById("videos").appendChild(videoWrapper);
    }
    document.getElementById("video-" + remoteId).srcObject = event.streams[0];
};
    }

function sendSignal(data) {
    fetch("/signal", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ ...data, room: roomId, username: username })
    });
}
</script>
</body>
</html>
