export default function ApiHeader() {
    // let token = JSON.parse(window.localStorage.getItem("token"));
    let token = "6013|t2aIEj61qSORvpZwqkA0sVOytfxZC8EaWendlPMK";
    const header = {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        'Content-type': 'multipart/form-data',
      },
    };
    return header;
  }
  