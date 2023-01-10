export default function ApiHeader() {
    // let token = JSON.parse(window.localStorage.getItem("token"));
    let token = "4712|sdFWwZdOC7RT8K5g2tb2upCFbkUH5IealrKxFhXW";
    const header = {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        'Content-type': 'multipart/form-data',
      },
    };
    return header;
  }
  