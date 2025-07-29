class Message {
    static success(message) {
        if (!message || message === '') {
            return;
        }
        Toastify({
            text: message,
            duration: 4000,
            close: true,
            gravity: "bottom",
            position: "right",
            backgroundColor: "#96c93d",
        }).showToast();
    }

    static error(message) {
        if (!message || message === '') {
            return;
        }
        Toastify({
            text: message,
            duration: 4000,
            close: true,
            gravity: "bottom",
            position: "right",
            backgroundColor: " #ff5f6d",
        }).showToast();
    }
}