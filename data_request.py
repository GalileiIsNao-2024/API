import requests

def get_data(url, credentials):
    response = requests.post(url, data=credentials)
    if response.ok:
        return response.json()
    else:
        raise Exception("Errore. ", response.status_code)


url = "https://www.bitlusion.com/nao/send_nao.php"
data = {
    "request": "addToCart",
    "user": "galileiisnao",
    "product": "rosa"
}

data = {
    "request": "checkout",
    "user": "galileiisnao",
}

data = {
    "request": "allUsers"
}

print(get_data(url, data))
