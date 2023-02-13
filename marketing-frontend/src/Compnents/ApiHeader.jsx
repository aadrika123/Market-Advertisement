export default function ApiHeader() {
    // let token = JSON.parse(window.localStorage.getItem("token"));
    let token = "6927|fYOxJyRC02gWokJxlbSJmBhOD0IAHZC8wzswyVOW";
    const header = {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        'Content-type': 'multipart/form-data',
      },
    };
    return header;
  }
  