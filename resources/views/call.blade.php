<!DOCTYPE html>
<html>
<head>
    <title>WebRTC Call</title>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
</head>
<body>
    <h2>Simple Call Demo</h2>

    <video id="localVideo" autoplay playsinline muted width="300"></video>
    <video id="remoteVideo" autoplay playsinline width="300"></video>

    <button onclick="startCall()">Start Call</button>

<script>
let localStream, peerConnection;
let userId = Math.floor(Math.random() * 1000); // mock user id
let targetUser = (userId === 1 ? 2 : 1); // simple 2 users demo

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: "{{ env('PUSHER_APP_KEY') }}",
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
});

window.Echo.channel('webrtc-channel-' + userId)
    .listen('WebRTCSignaling', async (e) => {
        handleSignal(e.data);
    });

async function startCall() {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
    document.getElementById("localVideo").srcObject = localStream;

    peerConnection = new RTCPeerConnection();

    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

    peerConnection.ontrack = (event) => {
        document.getElementById("remoteVideo").srcObject = event.streams[0];
    };

    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            sendSignal({ type: "candidate", candidate: event.candidate });
        }
    };

    let offer = await peerConnection.createOffer();
    await peerConnection.setLocalDescription(offer);
    sendSignal({ type: "offer", offer: offer });
}

async function handleSignal(data) {
    if (!peerConnection) {
        peerConnection = new RTCPeerConnection();

        peerConnection.ontrack = (event) => {
            document.getElementById("remoteVideo").srcObject = event.streams[0];
        };
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                sendSignal({ type: "candidate", candidate: event.candidate });
            }
        };
    }

    if (data.type === "offer") {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        document.getElementById("localVideo").srcObject = localStream;
        localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
        let answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        sendSignal({ type: "answer", answer: answer });
    } else if (data.type === "answer") {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
    } else if (data.type === "candidate") {
        await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
    }
}

function sendSignal(data) {
    fetch("/signal", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ ...data, to: targetUser })
    });
}
</script>
</body>
</html>
