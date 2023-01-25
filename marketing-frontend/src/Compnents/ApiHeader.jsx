export default function ApiHeader() {
    // let token = JSON.parse(window.localStorage.getItem("token"));
    let token = "5562|B7szgUHWECIzoqd2xTJKPuf1InhPGT9jpziMijZC";
    const header = {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        'Content-type': 'multipart/form-data',
      },
    };
    return header;
  }
  