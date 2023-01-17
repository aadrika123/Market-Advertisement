export default function ApiHeader() {
    // let token = JSON.parse(window.localStorage.getItem("token"));
    let token = "5122|TqFVoP2bPpe4I2IVRjNErDUoUbsM6cjFNYG48Yt1";
    const header = {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        'Content-type': 'multipart/form-data',
      },
    };
    return header;
  }
  