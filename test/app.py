from flask import Flask, render_template, request, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager, UserMixin, login_user, login_required, logout_user, current_user
import os
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)
app.config['SECRET_KEY'] = os.getenv('SECRET_KEY', 'your-secret-key')
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///home_assistant.db'
db = SQLAlchemy(app)
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = 'login'

class User(UserMixin, db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    password = db.Column(db.String(120), nullable=False)
    services = db.relationship('Service', backref='user', lazy=True)

class Service(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(80), nullable=False)
    service_type = db.Column(db.String(50), nullable=False)
    api_key = db.Column(db.String(200))
    user_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)

@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/services')
@login_required
def services():
    return render_template('services.html', services=current_user.services)

@app.route('/add_service', methods=['POST'])
@login_required
def add_service():
    data = request.json
    new_service = Service(
        name=data['name'],
        service_type=data['type'],
        api_key=data.get('api_key'),
        user_id=current_user.id
    )
    db.session.add(new_service)
    db.session.commit()
    return jsonify({'message': 'Сервис успешно добавлен'})

@app.route('/order_food', methods=['POST'])
@login_required
def order_food():
    # Здесь будет интеграция с Яндекс.Еда
    return jsonify({'message': 'Заказ еды отправлен'})

@app.route('/play_music', methods=['POST'])
@login_required
def play_music():
    # Здесь будет интеграция с музыкальными сервисами
    return jsonify({'message': 'Музыка запущена'})

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    app.run(debug=True) 